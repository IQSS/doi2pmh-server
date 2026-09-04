<?php


namespace App\Exceptions;


use Exception;

class CitationNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('admin.error.doi.citation.notFound', 404);
    }
}
