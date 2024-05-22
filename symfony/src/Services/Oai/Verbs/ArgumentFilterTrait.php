<?php

namespace App\Services\Oai\Verbs;

use App\Entity\Doi;
use App\Services\Oai\Arguments\ResumptionToken;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Trait used to filter results
 * Trait ArgumentFilterTrait
 * @package App\Services\Oai\Verbs
 */
trait ArgumentFilterTrait
{

    private CacheInterface $cache;

    /**
     * Get DOIs
     * @return array
     */
    public function getDois(): array
    {
        $expirationDate = $this->getArguments()['resumptionToken']->getExpirationDate();
        $dois = $this->getCache()->get(str_replace(":", "", $expirationDate), function(CacheItemInterface $cacheItem) {
            $cacheItem->expiresAfter(ResumptionToken::SECONDS_BEFORE_EXPIRATION);
            return $this->filter();
        });
        $dois = $this->filterCursor($dois);
        return $dois->toArray();
    }

    /**
     * Filter results with all filter arguments
     */
    public function filter(): ArrayCollection
    {
        $dois = new ArrayCollection();

        if (isset($this->getArguments()['set']) && $this->getArguments()['set']->getvalue()) {
            $dois = $this->filterSet($dois);
        } else {
            $dois = new ArrayCollection($this->getEntityManager()->getRepository(Doi::class)->findAll());
        }

        if (isset($this->getArguments()['from']) && $this->getArguments()['from']->getvalue()) {
            $dois = $this->filterFrom($dois);
        }

        if (isset($this->getArguments()['until']) && $this->getArguments()['until']->getvalue()) {
            $dois = $this->filterUntil($dois);
        }
        return $dois;
    }

    /**
     *  Filter by folder (set)
     */
    private function filterSet()
    {
        return new ArrayCollection($this->getArguments()['set']->getFolder()->getDoisChildren());
    }

    /**
     *  Filter by latest change (modification, creation, deletion)
     */
    private function filterFrom(ArrayCollection $dois) : ArrayCollection
    {
        // Normalize from value to respect the granularity YYYY-MM-DD
        $fromDateTime = new DateTime();
        $fromDateTime->setTimestamp(strtotime($this->getArguments()['from']->getValue()));
        $fromDateTime->setTime(0, 0);
        $fromValue = $fromDateTime->getTimestamp();

        /** @var Doi $doi */
        return $dois->filter(function ($doi) use ($fromValue){
            return $doi->getMostRecentChange()->getTimestamp() >= $fromValue;
        });
    }

    /**
     *  Filter by latest change (modification, creation, deletion)
     */
    public function filterUntil(ArrayCollection $dois) : ArrayCollection
    {
        // Normalize until value to respect the granularity YYYY-MM-DD
        $untilDateTime = new DateTime();
        $untilDateTime->setTimestamp(strtotime($this->getArguments()['until']->getValue()));
        $untilDateTime->setTime(23, 59, 999);
        $untilValue = $untilDateTime->getTimestamp();

        /** @var Doi $doi */
        return $dois->filter(function ($doi) use ($untilValue){
            return $doi->getMostRecentChange()->getTimestamp() <= $untilValue;
        });
    }

    /**
     *  Paginate with the resumption token cursor
     */
    public function filterCursor(ArrayCollection $dois) : ArrayCollection
    {
        $criteria = new Criteria();

        $criteria->setFirstResult($this->getArguments()['resumptionToken']->getCursor())->setMaxResults(ResumptionToken::ELEMENTS_PER_PAGE);

        return $dois->matching($criteria);
    }

    private function getCache(): CacheInterface
    {
        if (!isset($this->cache))
        {
            $this->cache = new FilesystemAdapter();
        }
        return $this->cache;
    }
}
