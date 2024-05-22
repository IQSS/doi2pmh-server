<?php

namespace App\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;

class DoiTest extends TestCase
{
    public function testGetMostRecentChangeOnlyCreated() : void
    {
        //With
        $doi = new Doi();
        $doi->setCreatedAt(new DateTime('31-10-2007'));

        //When
        $mostRecent = $doi->getMostRecentChange();

        //Then
        $this->assertEquals($doi->getCreatedAt(), $mostRecent);
    }

    public function testGetMostRecentChangeUpdated() : void
    {
        //With
        $doi = new Doi();
        $doi->setCreatedAt(new DateTime('31-10-2007T07:18:50'));
        $doi->setUpdatedAt(new DateTime('31-10-2007T07:19:00'));

        //When
        $mostRecent = $doi->getMostRecentChange();

        //Then
        $this->assertEquals($doi->getUpdatedAt(), $mostRecent);
    }

    public function testGetMostRecentChangeDeleted() : void
    {
        //With
        $doi = new Doi();
        $doi->setCreatedAt(new DateTime('31-10-2007T07:18:50'));
        $doi->setUpdatedAt(new DateTime('31-10-2007T07:19:00'));
        $doi->setDeletedAt(new DateTime('31-10-2007T07:19:01'));

        //When
        $mostRecent = $doi->getMostRecentChange();

        //Then
        $this->assertEquals($doi->getDeletedAt(), $mostRecent);
    }

    public function testGetMostRecentChangeRecreated() : void
    {
        //With
        $doi = new Doi();
        $doi->setCreatedAt(new DateTime('01-11-2007T06:00:00'));
        $doi->setDeletedAt(new DateTime('31-10-2007T07:19:01'));

        //When
        $mostRecent = $doi->getMostRecentChange();

        //Then
        $this->assertEquals($doi->getCreatedAt(), $mostRecent);
    }
}
