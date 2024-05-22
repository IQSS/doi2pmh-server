<?php


namespace App\Exceptions;


use Exception;
use Throwable;

class CitationNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('admin.error.doi.citation.notFound', 404);
    }
}
