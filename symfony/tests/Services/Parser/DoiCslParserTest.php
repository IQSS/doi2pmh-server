<?php

namespace App\Tests\Services\Parser;

use stdClass;
use PHPUnit\Framework\TestCase;
use App\Services\Parser\DoiCslParser;

class DoiCslParserTest extends TestCase {

    public function testIdentifier() : void {
        //With
        $cslParser = new DoiCslParser();
        $data = new stdClass();
        $data->identifier = "testIdentifier";

        //When
        $doi = $cslParser->buildDoiFrom($data);

        //Then
        $this->assertEquals("testIdentifier", $doi->identifier);
    }

    public function testIssuedDatepartsFullDate() : void {
        //With
        $cslParser = new DoiCslParser();
        $data = new stdClass();
        $data->identifier = "testIdentifier";
        $data->issued = new stdClass();
        $data->issued->{'date-parts'} = [["2005", "4", "12"]];

        //When
        $doi = $cslParser->buildDoiFrom($data);

        //Then
        $this->assertEquals('2005-04-12T00:00:00+00:00', $doi->datestamp);
    }

    public function testIssuedDatepartsRange() : void {
        //With
        $cslParser = new DoiCslParser();
        $data = new stdClass();
        $data->identifier = "testIdentifier";
        $data->issued = new stdClass();
        $data->issued->{'date-parts'} = [["2005", "4", "12"], ["2005", "6", "12"]];

        //When
        $doi = $cslParser->buildDoiFrom($data);

        //Then
        $this->assertEquals('2005-04-12T00:00:00+00:00', $doi->datestamp);
    }

    public function testIssuedDatepartsOnlyYear() : void {
        //With
        $cslParser = new DoiCslParser();
        $data = new stdClass();
        $data->identifier = "testIdentifier";
        $data->issued = new stdClass();
        $data->issued->{'date-parts'} = [["2005"]];

        //When
        $doi = $cslParser->buildDoiFrom($data);

        //Then
        $this->assertEquals('2005-01-01T00:00:00+00:00', $doi->datestamp);
    }

    public function testIssuedDatepartsOnlyYearAndMonth() : void {
        //With
        $cslParser = new DoiCslParser();
        $data = new stdClass();
        $data->identifier = "testIdentifier";
        $data->issued = new stdClass();
        $data->issued->{'date-parts'} = [["2005", "03"]];

        //When
        $doi = $cslParser->buildDoiFrom($data);

        //Then
        $this->assertEquals('2005-03-01T00:00:00+00:00', $doi->datestamp);
    }

    public function testIssuedRaw() : void {
        //With
        $cslParser = new DoiCslParser();
        $data = new stdClass();
        $data->identifier = "testIdentifier";
        $data->issued = new stdClass();
        $data->issued->{'raw'} = "2005-4-12";

        //When
        $doi = $cslParser->buildDoiFrom($data);

        //Then
        $this->assertEquals('2005-04-12T00:00:00+00:00', $doi->datestamp);
    }

    public function testIssuedRawMultiples() : void {
        //With
        $cslParser = new DoiCslParser();
        $data = new stdClass();
        $data->identifier = "testIdentifier";
        $data->issued = new stdClass();
        $data->issued->{'raw'} = "2005-3-15/2005-3-17";

        //When
        $doi = $cslParser->buildDoiFrom($data);

        //Then
        $this->assertEquals('2005-03-15T00:00:00+00:00', $doi->datestamp);
    }

}