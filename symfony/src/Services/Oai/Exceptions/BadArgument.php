<?php

namespace App\Services\Oai\Exceptions;

/**
 * Used if missing argument or if an argument is too much
 * Class BadArgument
 * @package App\Services\Oai\Exceptions
 */
class BadArgument extends OaiException
{
    public function __construct()
    {
        parent::__construct('badArgument', 'Illegal arguments or is missing required arguments');
    }
}
