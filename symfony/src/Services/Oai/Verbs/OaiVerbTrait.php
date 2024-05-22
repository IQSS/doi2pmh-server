<?php

namespace App\Services\Oai\Verbs;

use DOMElement;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Doi;
use App\Services\FolderService;
use App\Services\Oai\Arguments\ArgumentInterface;
use App\Services\Oai\OaiService;

/**
 * Trait OaiVerbTrait
 * @package App\Services\Oai\Verbs
 */
trait OaiVerbTrait
{
    private OaiService $oaiService;

    private Request $request;

    private EntityManagerInterface $entityManager;

    /**
     * @var ArgumentInterface[]
     */
    private array $arguments = [];

    /**
     * @see OaiVerbInterface
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @see OaiVerbInterface
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @see OaiVerbInterface
     * @param OaiService $oaiService
     * @return $this
     */
    public function setOaiService(OaiService $oaiService): self
    {
        $this->oaiService = $oaiService;
        return $this;
    }

    /**
     * @see OaiVerbInterface
     * @return OaiService
     */
    public function getOaiService(): OaiService
    {
        return $this->oaiService;
    }

    /**
     * @see OaiVerbInterface
     * @param EntityManagerInterface $entityManager
     * @return $this
     */
    public function setEntityManager(EntityManagerInterface $entityManager): self
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * @see OaiVerbInterface
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Verify if the request contains the required verb's arguments
     * @param Request $request
     * @return bool
     */
    public function hasGoodArguments(Request $request): bool
    {
        $requestArguments = $request->query->all();
        foreach ($this->arguments as $argument) {
            if ($argument->isRequired() && !$argument->getValue()) {
                return false;
            }
            $name = isset($requestArguments[$argument->getName()]) ? $argument->getName() : 'amp;' . $argument->getName();
            unset($requestArguments[$name]);
        }
        return empty($requestArguments);
    }

    /**
     * @see OaiVerbInterface
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Return the Header tag with content, used for ListRecords, GetRecord and ListIdentifiers
     * @param Doi $doi
     * @return DOMElement
     */
    public function getHeader(Doi $doi): DOMElement
    {
        $folderService = new FolderService($this->entityManager);

        $deleted = $this->isDeleted($doi) ? ['status' => 'deleted'] : [];

        $header = $this->oaiService->createElement('header', null, $deleted);
        $header->appendChild($this->oaiService->createElement('identifier', $doi->getUri()));

        if ($date = $doi->getCreationDate()) {
            $header->appendChild($this->oaiService->createElement('datestamp', $date));
        }

        foreach ($folderService->getParents($doi->getFolder()) as $parent) {
            $header->appendChild($this->oaiService->createElement('setSpec', $parent->getSlug()));
        }

        $header->appendChild($this->oaiService->createElement('setSpec', $doi->getFolder()->getSlug()));
        return $header;
    }

    /**
     * Return the content for one record with oai_dc xml format
     * @param Doi $doi
     * @return DOMElement
     */
    public function getOaiDCContent(Doi $doi): DOMElement
    {
        // Contains doi's data
        $doiData = $doi->getContent();

        // Namespace and schema
        $oaidc = $this->oaiService->createElement('oai_dc:dc', null, [
            'xmlns:oai_dc' => "http://www.openarchives.org/OAI/2.0/oai_dc/",
            'xmlns:dc' => 'http://purl.org/dc/elements/1.1/',
            "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
            "xsi:schemaLocation" => "http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd"
        ]);

        foreach ($doiData as $tagName => $tagValue) {
            // Datestamp tag is only in header
            if ($tagName === 'datestamp') {
                continue;
            }

            if (is_array($tagValue)) {
                foreach ($tagValue as $value) {
                    $oaidc->appendChild($this->oaiService->createElement($tagName, $value, [], true));
                }
            } else {
                $oaidc->appendChild($this->oaiService->createElement($tagName, $tagValue, [], true));
            }
        }

        return $oaidc;
    }

    /**
     * Returns true if the DOI should be marked as deleted.
     */
    public function isDeleted(Doi $doi): bool{
        return $doi->isDeleted() || $doi->isToIgnore();
    }
}
