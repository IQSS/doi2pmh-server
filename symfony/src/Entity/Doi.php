<?php /** @noinspection PhpPropertyOnlyWrittenInspection */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\State\DoiProcessor;
use App\State\DoiProvider;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Doi as AcmeAssert;
use DateTime;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DoiRepository")
 * @ORM\HasLifecycleCallbacks
 */

 #[ApiResource(
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    provider:DoiProvider::class,
    normalizationContext:['groups' => [Doi::GROUP_READ], 'openapi_definition_name' => 'Read'],
    operations: [
        new Get(),
        new GetCollection(),
        new GetCollection(
            openapiContext:[
                'summary' => 'Retrieves the DOIs in the Folder.'
            ],
            uriTemplate: "folders/{id}/dois",
            uriVariables:[
                'id' => new Link(fromClass:Folder::class, toProperty:"folder")
            ])
        ]
)]
#[ApiResource(
    securityPostDenormalize: "user.hasRightsFor(object.getFolder())",
    provider:DoiProvider::class,
    processor:DoiProcessor::class,
    normalizationContext:['groups' => [Doi::GROUP_READ], 'openapi_definition_name' => 'Read'],
    operations: [
        new Post(
            read: false,
            denormalizationContext:['groups' => [Doi::GROUP_WRITE], 'openapi_definition_name' => 'Write']),
        new Delete(read: true)
        ]
)]
class Doi
{

    const GROUP_READ="doi:read";
    const GROUP_WRITE="doi:write";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    #[Groups([Doi::GROUP_READ])]
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Url
     * @AcmeAssert\DoiUrl
     */
    #[Groups([Doi::GROUP_READ, Doi::GROUP_WRITE])]
    private ?string $uri = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Folder", inversedBy="dois", fetch="EAGER")
     */
    #[Groups([Doi::GROUP_READ, Doi::GROUP_WRITE])]
    private ?Folder $folder;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[Groups([Doi::GROUP_READ])]
    private ?string $citation = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $jsonContent;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $deleted = false;

    /**
     * @ORM\Column(type="boolean")
     * Was the DOI marked as toIgnore when it was last refreshed?
     */
    private bool $toIgnore = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $updatedAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $deletedAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getFolder(): Folder
    {
        return $this->folder;
    }

    public function setFolder(?Folder $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getCitation(): ?string
    {
        return $this->citation;
    }

    public function setCitation(string $citation): self
    {
        $this->citation = $citation;

        return $this;
    }

    /**
     * @Ignore
     * @return Object|null
     */
    public function getContent(): ?Object
    {
        return json_decode($this->jsonContent);
    }

    /**
     * @param string|null $jsonContent
     */
    public function setJsonContent(?string $jsonContent): void
    {
        $this->jsonContent = $jsonContent;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    /**
     * @return bool
     */
    public function isToIgnore(): bool
    {
        return $this->toIgnore;
    }

    public function setToIgnore(bool $toIgnore):void
    {
        $this->toIgnore = $toIgnore;
    }


    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Return the creation date of data
     * @return string|null
     */
    public function getCreationDate(): ?string
    {
        $content = $this->getContent();
        // Date
        if (isset($content->datestamp)) {
           return $content->datestamp;
        }
        return $this->getCreatedAt()->format('Y-m-d');
    }

    /**
     * Returns the time of the most recent change on this DOI in DOI2PMH.
     * @return DateTimeInterface
     */
    public function getMostRecentChange(): DateTimeInterface
    {
        $mostRecent = $this->getCreatedAt();
        if ($this->getUpdatedAt() && $this->getUpdatedAt()->diff($mostRecent)->invert){
            $mostRecent = $this->getUpdatedAt();
        }
        if ($this->getDeletedAt() && $this->getDeletedAt()->diff($mostRecent)->invert){
            $mostRecent = $this->getDeletedAt();
        }
        return $mostRecent;
    }

    /**
     * Return Doi uniq ID : 10.1111/XXXX
     * @return string
     */
    public function getDoiUniqId(): string
    {
        return ltrim(parse_url($this->getUri(), PHP_URL_PATH), '/');
    }

    /**
     * Return the types
     */
    public function getTypes(): array
    {
        if ($this->getContent() && isset($this->getContent()->type)){
            $type = $this->getContent()->type;
            return is_array($type) ? $type : [$type];
        }
        return [];
    }

     /**
     * @ORM\PrePersist
     */
    public function initCreatedAt(): void
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new DateTime('now'));
        }
    }

}
