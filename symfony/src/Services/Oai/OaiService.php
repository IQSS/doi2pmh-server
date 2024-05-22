<?php

namespace App\Services\Oai;

use DOMDocument;
use DOMElement;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Oai\Exceptions\BadArgument;
use App\Services\Oai\Exceptions\BadResumptionToken;
use App\Services\Oai\Exceptions\CannotDisseminateFormat;
use App\Services\Oai\Exceptions\BadVerb;
use App\Services\Oai\Verbs\OaiVerbInterface;

class OaiService {

    const VERB_CLASSES_PREFIX = 'App\Services\Oai\Verbs\\';

    private array $classesVerb = [];

    private DOMDocument $dom;

    public UrlGeneratorInterface $router;

    public function __construct(
        UrlGeneratorInterface $router
    )
    {
        $this->router = $router;
        $this->dom = new DOMDocument();

        $this->classesVerb['identify'] = self::VERB_CLASSES_PREFIX . 'Identify';
        $this->classesVerb['listidentifiers'] = self::VERB_CLASSES_PREFIX . 'ListIdentifiers';
        $this->classesVerb['listrecords'] = self::VERB_CLASSES_PREFIX . 'ListRecords';
        $this->classesVerb['listmetadataformats'] = self::VERB_CLASSES_PREFIX . 'ListMetadataFormats';
        $this->classesVerb['listsets'] = self::VERB_CLASSES_PREFIX . 'ListSets';
        $this->classesVerb['getrecord'] = self::VERB_CLASSES_PREFIX . 'GetRecord';
    }

    /**
     * Return the verb class or a badVerb exception if not exist.
     * Set verb's arguments with query and resumption token and return appropriate oai exception.
     * @param Request $request
     * @return OaiVerbInterface
     */
    public function getVerb(Request $request): OaiVerbInterface
    {
        if ($request->get('verb') === null || !isset($this->classesVerb[strtolower($request->get('verb'))])) {
            return (new BadVerb())->setRequest($request);
        }

        /**
         * @var OaiVerbInterface
         */
        $verb = new $this->classesVerb[strtolower($request->get('verb'))]();
        $verb->setRequest($request);
        $verb->setArguments();

        if (isset($verb->getArguments()['resumptionToken']) && !is_null($request->get('resumptionToken'))) {
            try {
                $verb->getArguments()['resumptionToken']->parseTokenContent();
            } catch (Exception $exception) {
                return (new BadResumptionToken())->setRequest($request);
            }
        }

        if (isset($verb->getArguments()['metadataPrefix']) && !$verb->getArguments()['metadataPrefix']->isAllowedFormat()) {
            return (new CannotDisseminateFormat())->setRequest($request);
        }

        if (!$verb->hasGoodArguments($request)) {
            return (new BadArgument())->setRequest($request);
        }

        return $verb;
    }

    /**
     * @return DOMDocument
     */
    public function getDom(): DOMDocument
    {
        return $this->dom;
    }

    /**
     * Creat a xml element with its attributes
     * @param string $name
     * @param string|null $value
     * @param array $attributes
     * @param bool $isOaiDcTag
     * @return DOMElement
     */
    public function createElement(string $name, ?string $value = null, array $attributes = [], bool $isOaiDcTag = false): DOMElement
    {
        $name = $isOaiDcTag ? 'dc:' . $name : $name;

        /**
         * @var DOMElement
         */
        $element = $this->dom->createElement($name, htmlspecialchars($value ?? ''));

        foreach ($attributes as $attrName => $attrValue) {
            $element = $this->addAttribute($element, $attrName, $attrValue);
        }

        return $element;
    }

    /**
     * Return the oai root tag
     * @param Request $request
     * @return DOMElement
     */
    public function getRootTag(Request $request): DOMElement
    {
        $rootTag = $this->createElement('OAI-PMH', null, [
            'xmlns' => "http://www.openarchives.org/OAI/2.0/",
            'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
            'xsi:schemaLocation' => "http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd"
        ]);
        $rootTag->appendChild($this->createElement('responseDate', date('c')));
        $rootTag->appendChild($this->createElement(
            'request',
            $this->router->generate('oai_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $request->query->all()
        ));
        return $rootTag;
    }

    /**
     * Insert root tag content into root tag, format xml and return response with it
     * @param Request $request
     * @param DOMElement $rootTagContent
     * @return Response
     */
    public function formatResponse(Request $request, DOMElement $rootTagContent): Response
    {
        $rootTag = $this->getRootTag($request);
        $rootTag->appendChild($rootTagContent);
        $this->dom->appendChild($rootTag);
        $this->dom->formatOutput = true;
        $response = new Response($this->dom->saveXML());
        $response->headers->set('content-type','application/xml; charset=UTF-8');
        return $response;
    }

    /**
     * Return attribute for xml tag
     * @param DOMElement $tag
     * @param string $attributeName
     * @param string $attributeValue
     * @return DOMElement
     */
    public function addAttribute(DOMElement $tag, string $attributeName, string $attributeValue): DOMElement
    {
        $attribute = $this->dom->createAttribute($attributeName);
        $attribute->value = $attributeValue;
        $tag->appendChild($attribute);
        return $tag;
    }
}
