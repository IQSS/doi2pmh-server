<?php

namespace App\Services\Oai\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use App\Services\Oai\Verbs\OaiVerbTrait;
use App\Services\Oai\Verbs\OaiVerbInterface;

/**
 * Abstract base class for all oai exception
 * Class OaiException
 * @package App\Services\Oai\Exceptions
 */
abstract class OaiException implements OaiVerbInterface
{
    use OaiVerbTrait;

    const ERROR_TAG_NAME = 'error';

    private string $oaiErrorCode;

    private string $message;

    public function __construct(
        string $errorCode,
        string $message
    )
    {
        $this->oaiErrorCode = $errorCode;
        $this->message = $message;
    }

    /**
     * @see OaiVerbInterface
     * @return Response
     */
    public function getXmlResponse(): Response
    {
        $error = $this->oaiService->createElement(self::ERROR_TAG_NAME, $this->message, [
            'code' => $this->oaiErrorCode
        ]);

        return $this->oaiService->formatResponse($this->request, $error);
    }

    /**
     * @see OaiVerbInterface
     * @return array
     */
    public function setArguments(): array
    {
        return [];
    }
}
