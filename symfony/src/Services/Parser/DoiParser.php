<?php

namespace App\Services\Parser;

use stdClass;

interface DoiParser {

    /** Returns a stdClass description of a DOI based on another description format (CSL or Dataverse). */
    public function buildDoiFrom(stdClass $data): stdClass;

}
