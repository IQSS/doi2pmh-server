<?php

namespace App\Services\Oai\Verbs;

use DOMElement;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Oai\Arguments\ResumptionToken;
use App\Services\Oai\Arguments\Verb;
use App\Entity\Folder;

/**
 * Represent ListSets oai verb
 * ListSets return all folder name and there slug
 * Class ListSets
 * @package App\Services\Oai\Verbs
 */
class ListSets implements OaiVerbInterface
{
    use OaiVerbTrait, ResumptionTokenTrait;

    /**
     * @see OaiVerbInterface
     */
    public function setArguments()
    {
        $this->arguments = [
            'verb' => new Verb($this),
            'resumptionToken' => new ResumptionToken($this)
        ];
    }

    /**
     * @see OaiVerbInterface
     * @return Response
     */
    public function getXmlResponse(): Response
    {
        $listSets = $this->oaiService->createElement('ListSets');

        /**
         * @var Folder[]
         */
        $folders = $this->entityManager->getRepository(Folder::class)->findAtPage( $this->arguments['resumptionToken']->getCursor());

        foreach ($folders as $folder) {
            $listSets->appendChild($this->getSet($folder->getSlug(), $folder->getName()));
        }

        $this->updateResumptionToken($this->entityManager->getRepository(Folder::class)->count([]));

        // Generate the resumption token content if is necessary
        if ($this->arguments['resumptionToken']->shouldBeIncluded()) {
            $listSets->appendChild($this->arguments['resumptionToken']->generateToken());
        }

        return $this->oaiService->formatResponse($this->request, $listSets);
    }

    public function getSet(string $slug, string $name): DOMElement
    {
        $set = $this->oaiService->createElement('set');
        $set->appendChild($this->oaiService->createElement('setSpec', $slug));
        $set->appendChild($this->oaiService->createElement('setName', $name));
        return $set;
    }
}
