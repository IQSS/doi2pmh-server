<?php

namespace App\Services\Parser;

use stdClass;

class DoiDataverseParser implements DoiParser {

    public function buildDoiFrom(stdClass $data): stdClass
    {
        $doiData = new stdClass();

        // Identifier
        $doiData->identifier = $data->persistentUrl;

        // Publisher
        $doiData->publisher = $data->publisher;

        // Datestamp
        $doiData->datestamp = $data->datasetVersion->lastUpdateTime;
        if (isset($doiData->datestamp) && strlen($doiData->datestamp) >= 4) {
            $doiData->date = substr($doiData->datestamp, 0, 4);
        }

        $subjects = [];
        foreach ($data->datasetVersion->metadataBlocks->citation->fields as $field) {
            // Title
            if ($field->typeName === 'title') {
                $doiData->title = htmlspecialchars_decode($field->value);
            }

            // Authors
            if ($field->typeName === 'author') {
                $authors = [];
                foreach ($field->value as $author) {
                    $authors[] = $author->authorName->value;
                }
                $doiData->creator = $authors;
            }

            // Abstract
            if ($field->typeName === 'dsDescription') {
                $doiData->description = $field->value[0]->dsDescriptionValue->value;
            }

            // Subject
            if ($field->typeName === 'subject' || $field->typeName === 'keyword') {
                foreach ($field->value as $subject) {
                    $subjects[] = $field->typeName === 'subject' ? $subject : $subject->keywordValue->value;
                }
            }

            // Language
            if ($field->typeName === 'language') {
                $languages = [];
                foreach ($field->value as $language) {
                    $languages[] = $language;
                }
                $doiData->language = $languages;
            }

            // Contributor
            if ($field->typeName === 'datasetContact') {
                $contributors = [];
                foreach ($field->value as $contributor) {
                    $contributors[] = $contributor->datasetContactName->value;
                }
                $doiData->contributor = $contributors;
            }

            // Type
            if ($field->typeName === 'kindOfData') {
                $types = [];
                foreach ($field->value as $type) {
                    $types[] = $type;
                }
                $doiData->type = $types;
            }
        }
        $doiData->subject = $subjects;

        return $doiData;
    }

}
