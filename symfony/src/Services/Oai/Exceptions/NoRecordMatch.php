<?php

namespace App\Services\Oai\Exceptions;

/**
 * Used if no records found
 * Class NoRecordMatch
 * @package App\Services\Oai\Exceptions
 */
class NoRecordMatch extends OaiException
{
    public function __construct()
    {
        parent::__construct('noRecordsMatch', 'No Records Match.');
    }
}
