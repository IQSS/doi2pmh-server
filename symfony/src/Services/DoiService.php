<?php

namespace App\Services;

use App\Entity\Configuration;
use App\Entity\Doi;
use App\Services\Parser\DoiCslParser;
use App\Services\Parser\DoiDataverseParser;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DoiService {

    private CurlHttpClient $http;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private DoiDataverseParser $dataverseParser,
        private DoiCslParser $cslParser,
        private ConfigurationService $configurationService
    )
    {
        $this->http = new CurlHttpClient();
    }

    /**
     * Send request to doi.org return true if doi exist
     * @param string $uri
     * @return bool
     */
    public function doiExist(string $uri): bool
    {
        try {
            return $this->http->request(
                'GET',
                $uri
            )->getStatusCode() !== Response::HTTP_NOT_FOUND;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    /**
     * Returns the old deleted DOI matching that URI if it already exists, or the same DOI if not.
     * @param Doi $doi
     * @return Doi
     */
    public function replaceIfDeleted(Doi $doi): Doi
    {
        $existingDoi = $this->entityManager->getRepository(Doi::class)->findOneBy(['uri' => $doi->getUri()]);

        // If it has been deleted update it
        if ($existingDoi && $existingDoi->isDeleted()) {
            $existingDoi->setDeleted(false);
            return $existingDoi;
        }
        return $doi;
    }

    /**
     * Delete a DOI.
     * @param Doi $doi
     */
    public function deleteDoi(Doi $doi): void
    {
        $doi->setDeleted(true);
        $decodedContent = $doi->getContent();
        $decodedContent->created =  date('Y-m-d', strtotime('now'));
        $doi->setJsonContent(json_encode($decodedContent));
        $this->entityManager->persist($doi);
        $this->entityManager->flush();
    }

    /**
     * @param Doi $doi
     * @return Doi
     */
    public function fillDoiData(Doi $doi): Doi{
        $data = $this->getDoiData($doi);
        if ($data) {
            $doi->setJsonContent($data);
            $doi->setToIgnore($this->configurationService->isExcludedType($doi->getTypes()));
        } 
        return $doi;
    }

    /**
     * @param Doi $doi
     * @param int $retry
     * @return string
     */
    private function getDoiData(Doi $doi, int $retry = 0): ?string
    {
        try {
            $request = $this->http->request(
                'GET',
                $doi->getUri(),
                ['headers' => ['Accept' => 'application/vnd.citationstyles.csl+json; q=1']]
            );

            $content = json_decode($request->getContent());
            $content->identifier = $doi->getUri();

            // If is a dataset, need more information
            if (isset($content->type) && $content->type === 'dataset') {
                $dataverseApiUrl = 'https://' . parse_url($content->URL, PHP_URL_HOST) . '/api/datasets/export?exporter=dataverse_json&persistentId=doi:' . $doi->getDoiUniqId();
                try {
                    $request = $this->http->request(
                        'GET',
                        $dataverseApiUrl
                    );
                    $decodedContent = json_decode($request->getContent());

                    if (!is_object($decodedContent)) {
                        throw new Exception('Not found');
                    }

                    $doiData = $this->dataverseParser->buildDoiFrom($decodedContent);

                    return json_encode($doiData);
                }catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | Exception $e) {
//                    dd($e->getMessage());
                    // Nothing to do
                }
            }

            $doiData = $this->cslParser->buildDoiFrom($content);

            return json_encode($doiData);

        } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
            $retry++;
            return $retry < 3 ? $this->getDoiData($doi, $retry) : null;
        }
    }

    /**
     * @param Doi $doi
     * @param int $retry
     * @return string
     */
    public function getCitation(Doi $doi, int $retry = 0): ?string
    {
        try {
            $request = $this->http->request(
                'GET',
                $doi->getUri(),
                ['headers' => ['Accept' => 'text/x-bibliography; style=harvard-cite-them-right; q=1']]
            );
            return $request->getContent();

        } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
            $retry++;
            return $retry < 3 ? $this->getCitation($doi, $retry) : null;
        }
    }

    /**
     * Send request to datacite to refresh doi's data
     * Used with button and command line
     * @param OutputInterface|null $output
     * @return int
     */
    public function refreshDois(OutputInterface $output= null): int
    {
        $dois = $this->entityManager->getRepository(Doi::class)->findAll(false);
        $count = 0;

        if (!$output) {
            ob_implicit_flush(true);
            echo "data: " . json_encode(['progress' => 0, 'total' => count($dois)]) . PHP_EOL . PHP_EOL;
            @ob_flush();
        }

        /**
         * @var DOI $doi
         */
        foreach ($dois as $doi) {
            $data = $this->getDoiData($doi);
            if ($data) {
                $count ++;
                $doi->setJsonContent($data);
                $doi->setToIgnore($this->configurationService->isExcludedType($doi->getTypes()));
                if ($output) {
                    $output->write('Updating ' . $doi->getUri() .  PHP_EOL);
                } else {
                    echo "data: " . json_encode(['progress' => $count, 'total' => count($dois)]) . PHP_EOL . PHP_EOL;
                    @ob_flush();
                }
                $this->entityManager->persist($doi);
            }
        }
        try {
            $logs = '[' . (new DateTime('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d H:i:s') . '] <span class="text-success">' . $count . ' doi(s) updated </span><span class="text-danger"> ' . (count($dois) - $count) . ' error(s)</span>';
            $configuration = Configuration::getConfigurationInstance($this->entityManager);
            $configuration->setUpdatedDoiLogs($logs);
            $this->entityManager->persist($configuration);
        } catch (Exception $e) {
        }

        $this->entityManager->flush();
        return $count;
    }
    
}
