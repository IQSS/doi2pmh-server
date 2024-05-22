<?php

namespace App\Services\Oai\Exceptions;

/**
 * Used if a doi is not found
 * Class IdDoesNotExist
 * @package App\Services\Oai\Exceptions
 */
class IdDoesNotExist extends OaiException
{
    public function __construct()
    {
        parent::__construct('idDoesNotExist', 'No matching identifier.');
    }
}
