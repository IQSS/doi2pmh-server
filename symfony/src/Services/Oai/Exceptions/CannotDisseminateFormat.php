<?php

namespace App\Services\Oai\Exceptions;

/**
 * Used if the metadataPrefix arguments is not oai_dc
 * Class CannotDisseminateFormat
 * @package App\Services\Oai\Exceptions
 */
class CannotDisseminateFormat extends OaiException
{
    public function __construct()
    {
        parent::__construct('cannotDisseminateFormat', 'The value of the metadataPrefix argument is not supported.');
    }
}
