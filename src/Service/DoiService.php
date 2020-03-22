<?php

namespace App\Service;

use App\Entity\Doi;
use App\Repository\DoiRepository;
use Symfony\Component\HttpClient\HttpClient;
use Doctrine\ORM\EntityManagerInterface;

class DoiService {

    private $em;
    private $repo;
    public function __construct(EntityManagerInterface $em, DoiRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
    }

    public function validate(Doi $doi, $force = false){
        if (!filter_var($doi->getUri(), FILTER_VALIDATE_URL)){
            return false;
        }
        if (!$this->exists($doi, $force)){
            return false;
        }
        return true;
    }

    public function isDuplicate(Doi $doi){
        $list = $this->repo->findByUri($doi->getUri());
        if (count($list) > 0){
            return true;
        }
        return false;
    }

    private function exists(Doi $doi, $force = false){

        if ($doi->isCached() && !$force){
            return true;
        }

        $client = HttpClient::create([
            // 0 means to not follow any redirect
            'max_redirects' => 0,
        ]);
        
        $response = $client->request('GET', $doi->getUri());
        $statusCode = $response->getStatusCode();
        return $statusCode == 302;
    }

    public function getCitation(Doi $doi, $force = false){
        if ($this->validate($doi) != true) {
            return "Not a valid doi";
        }

        if ($doi->isCached() && !$force){
            return $doi->getCitation();
        }

        $client = HttpClient::create(['headers' => [
            'Accept' => 'text/x-bibliography; style=harvard-cite-them-right',
        ]]);
        
        $response = $client->request('GET', $doi->getUri());
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200){
            return "Error formatting citation for doi ".$doi->getUri();
        }
        $doi->setCitation($response->getContent());
        $this->em->flush();
        return $doi->setCitation($response->getContent());
    }
}