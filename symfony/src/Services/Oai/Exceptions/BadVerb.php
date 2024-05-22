<?php

namespace App\Services\Oai\Exceptions;

/**
 * Used if the verb is not one of GetRecord, ListRecords, Identify, ListIdentifiers, ListMetadataFormats, ListSets
 * Class BadVerb
 * @package App\Services\Oai\Exceptions
 */
class BadVerb extends OaiException
{
    public function __construct()
    {
        parent::__construct('badVerb', 'Illegal OAI verb');
    }
}
