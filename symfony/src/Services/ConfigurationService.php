<?php

namespace App\Services;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;

class ConfigurationService {

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Return true if any of the data types are excluded from the OAI repository by the administrator
     * @param string|array $types
     */
    public function isExcludedType(string|array $types)
    {
        if (!is_array($types)){
            $types = [$types];
        }
        $types = array_map('strtolower', $types);
        $excludedTypes = $this->getConfiguration()->getExcludedTypes();
        return !empty(array_intersect($excludedTypes, $types));
    }

    /**
     * Return the app configuration.
     */
    public function getConfiguration(): Configuration
    {
        return Configuration::getConfigurationInstance($this->entityManager);
    }
    
}
