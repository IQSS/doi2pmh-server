<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConfigurationRepository")
 */
class Configuration
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $repositoryName = 'Doi2Pmh';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $adminEmail = null;

    /**
     * @ORM\Column(type="json")
     */
    private array $excludedTypes = [];

    /**
     * @ORM\Column(type="date", length=255)
     */
    private ?DateTime $earliestDatestamp = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $cas_authentication = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $casVersion = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $casHost = null;

    /**
     * @ORM\Column(type="integer", length=3, nullable=true)
     */
    private ?int $casPort = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $casUri = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $casServiceBaseUri = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $updatedDoiLogs;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }

    /**
     * @param string $repositoryName
     */
    public function setRepositoryName(string $repositoryName): void
    {
        $this->repositoryName = $repositoryName;
    }

    /**
     * @return string|null
     */
    public function getAdminEmail(): ?string
    {
        return $this->adminEmail;
    }

    /**
     * @param string|null $adminEmail
     */
    public function setAdminEmail(?string $adminEmail): void
    {
        $this->adminEmail = $adminEmail;
    }

    /**
     * @return array
     */
    public function getExcludedTypes(): array
    {
        return $this->excludedTypes;
    }

    /**
     * @param array $excludedTypes
     */
    public function setExcludedTypes(array $excludedTypes): void
    {
        $this->excludedTypes = array_map('strtolower', $excludedTypes);
    }

    /**
     * @return DateTime|null
     */
    public function getEarliestDatestamp(): ?DateTime
    {
        return $this->earliestDatestamp;
    }

    /**
     * @param DateTime|null $earliestDatestamp
     */
    public function setEarliestDatestamp(?DateTime $earliestDatestamp): void
    {
        $this->earliestDatestamp = $earliestDatestamp;
    }

    /**
     * Return the configuration instance
     * @param EntityManagerInterface $entityManager
     * @return Configuration
     */
    public static function getConfigurationInstance(EntityManagerInterface $entityManager): Configuration
    {
        /**
         * @var Configuration $configuration
         */
        $configurations = $entityManager->getRepository(Configuration::class)->findAll();

        if (empty($configurations) || get_class(reset($configurations)) !== Configuration::class) {
            $configuration = new Configuration();
        } else {
            $configuration = reset($configurations);
        }

        $configuration->setCasAuthentication(filter_var(getenv('ENABLE_CAS'), FILTER_VALIDATE_BOOLEAN));
        if ($configuration->isCasAuthentication()){
            $configuration->setCasHost(getenv('CAS_HOST'));
            $configuration->setCasVersion(getenv('CAS_VERSION'));
            $configuration->setCasUri(getenv('CAS_URI'));
            $configuration->setCasPort(getenv('CAS_PORT'));
            $configuration->setCasServiceBaseUri(getenv('APP_PROTOCOL').getenv('APP_DNS'));
        }
        return $configuration;
    }

    /**
     * @return bool
     */
    public function isCasAuthentication(): bool
    {
        return $this->cas_authentication;
    }

    /**
     * @param bool $cas_authentication
     */
    public function setCasAuthentication(bool $cas_authentication): void
    {
        $this->cas_authentication = $cas_authentication;
    }

    /**
     * @return string
     */
    public function getCasVersion(): string
    {
        return $this->casVersion;
    }

    /**
     * @param string $casVersion
     */
    public function setCasVersion(string $casVersion): void
    {
        $this->casVersion = $casVersion;
    }

    /**
     * @return string|null
     */
    public function getCasHost(): ?string
    {
        return $this->casHost;
    }

    /**
     * @param string|null $casHost
     */
    public function setCasHost(?string $casHost): void
    {
        $this->casHost = $casHost;
    }

    /**
     * @return int|null
     */
    public function getCasPort(): ?int
    {
        return $this->casPort;
    }

    /**
     * @param int|null $casPort
     */
    public function setCasPort(?int $casPort): void
    {
        $this->casPort = $casPort;
    }

    /**
     * @return string|null
     */
    public function getCasUri(): ?string
    {
        return $this->casUri;
    }

    /**
     * @param string|null $casUri
     */
    public function setCasUri(?string $casUri): void
    {
        $this->casUri = $casUri;
    }

    /**
     * @return string|null
     */
    public function getCasServiceBaseUri(): ?string
    {
        return $this->casServiceBaseUri;
    }

    /**
     * @param string|null $casUri
     */
    public function setCasServiceBaseUri(?string $casServiceBaseUri): void
    {
        $this->casServiceBaseUri = $casServiceBaseUri;
    }

    /**
     * @return string|null
     */
    public function getUpdatedDoiLogs(): ?string
    {
        return $this->updatedDoiLogs;
    }

    /**
     * @param string|null $updatedDoiLogs
     */
    public function setUpdatedDoiLogs(?string $updatedDoiLogs): void
    {
        $this->updatedDoiLogs = $updatedDoiLogs;
    }
}
