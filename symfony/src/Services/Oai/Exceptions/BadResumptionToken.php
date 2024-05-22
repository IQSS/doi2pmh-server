<?php

namespace App\Services\Oai\Exceptions;

/**
 * Used if the resumption token is invalid or expired
 * Class BadResumptionToken
 * @package App\Services\Oai\Exceptions
 */
class BadResumptionToken extends OaiException
{
    public function __construct()
    {
        parent::__construct('badResumptionToken', "The value of the resumptionToken argument is invalid or expired");
    }
}
