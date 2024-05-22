<?php

namespace App\Services\Parser;

use DateTimeImmutable;
use stdClass;

class DoiCslParser implements DoiParser {

    public function buildDoiFrom(stdClass $data): stdClass
    {
        $doiData = new stdClass();

        // Identifier
        $doiData->identifier = $data->identifier;

        // Publisher
        if (isset($data->publisher)) {
            $doiData->publisher = $data->publisher;
        }

        // Datestamp
        if (isset($data->created) && isset($data->created->{'date-time'})) {
            $doiData->datestamp = $data->created->{'date-time'};
        } elseif (isset($data->issued)) {
            $doiData->datestamp = $this->buildDateFromIssued($data->issued);
        }
        
        // Date
        if (isset($doiData->datestamp) && strlen($doiData->datestamp) >= 4) {
            $doiData->date = substr($doiData->datestamp, 0, 4);
        }

        // Title
        if (isset($data->title)) {
            $doiData->title = htmlspecialchars_decode($data->title);
        }

        // Authors
        if (isset($data->author)) {
            $authors = [];
            foreach ($data->author as $author) {
                if (isset($author->family)) {
                    $authors[] = $author->family . ', ' . $author->given;
                } elseif ($author->literal) {
                    $authors[] = $author->literal;
                }
            }
            $doiData->creator = $authors;
        }

        // Abstract
        if (isset($data->abstract)) {
            $doiData->description = $data->abstract;
        }

        // Subject
        if (isset($data->subject) || isset($data->categories)) {
            $propertyName = isset($data->subject) ? 'subject' : 'categories';
            $subjects = [];
            foreach ($data->$propertyName as $subject) {
                $subjects[] = $subject;
            }
            $doiData->subject = $subjects;
        }

        // Language
        if (isset($data->language)) {
            $doiData->language = $data->language;
        }

        // Contributor
        if (isset($data->contributor)) {
            $contributors = [];
            foreach ($data->contributor as $contributor) {
                if (isset($contributor->family)) {
                    $contributors[] = $contributor->family . ', ' . $contributor->given;
                } elseif ($contributor->literal) {
                    $contributors[] = $contributor->literal;
                } else {
                    $contributors[] = $contributor;
                }
            }
            $doiData->contributor = $contributors;
        }

        // Type
        if (isset($data->type)) {
            $doiData->type = $data->type;
        }

        return $doiData;
    }

    /**
     * Extract a date from the issued field.
     */
    private function buildDateFromIssued(stdClass $issued): string
    {
        if (isset($issued->{"date-parts"}))
        {
            return $this->buildDateFromDatepart($issued->{"date-parts"}[0]);
        }
        return $this->buildDateFromRaw($issued->raw);
    }


    /**
     * Extract a date in the date-parts format.
     */
    private function buildDateFromDatepart(array $dateParts): string
    {
        $dateStr = '';
        $partNbr = 0;
        foreach ($dateParts as $part) {
            $dateStr .= $part . '-';
            $partNbr++;
        }
        for ($i = $partNbr; $i < 3; $i++) {
            $dateStr .= '01-';
        }
        return date('Y-m-d', strtotime(rtrim($dateStr, '-')));
    }

    /**
     * Extract a date in the raw format.
     */
    private function buildDateFromRaw(string $raw): string
    {
        $date = explode("/", $raw)[0];
        return date('c', strtotime($date));
    }

}
