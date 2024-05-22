<?php /** @noinspection ALL */

namespace App\Entity;


use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\FolderProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FolderRepository")
 */
#[ApiResource(
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    operations:[
        new Get(
            normalizationContext:['groups' => [Folder::GROUP_READ_ONE], 'openapi_definition_name' => 'Read'],
            requirements: ['id' => '\d+'],
            uriTemplate:'folders/{id}'),
        new GetCollection(
            name: 'api_folder_all',
            normalizationContext:['groups' => [Folder::GROUP_READ], 'openapi_definition_name' => 'ReadAll'],
            provider: FolderProvider::class),
        new GetCollection(
            openapiContext:[
                'description' => 'Retrieves the Folders the authentified user can edit.',
                'summary' => 'Retrieves the Folders of the current user.'
            ],
            name: 'api_folder_me',
            normalizationContext:['groups' => [Folder::GROUP_READ], 'openapi_definition_name' => 'Read'],
            provider: FolderProvider::class,
            uriTemplate:'folders/me')
    ]
)]
class Folder
{

    const GROUP_READ="folder:read";
    const GROUP_READ_ONE="folder:get:read";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    #[Groups([Folder::GROUP_READ_ONE, Folder::GROUP_READ])]
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups([Folder::GROUP_READ_ONE, Folder::GROUP_READ])]
    private ?string $name = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Folder", inversedBy="children", cascade={"remove"}, fetch="EAGER")
     */
    #[Groups([Folder::GROUP_READ_ONE])]
    private ?Folder $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Folder", mappedBy="parent", cascade={"remove"})
     */
    #[Groups([Folder::GROUP_READ])]
    private Collection $children;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Doi", mappedBy="folder")
     */
    private Collection $dois;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="rootFolder")
     */
    private Collection $owners;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->dois = new ArrayCollection();
        $this->owners = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return (new AsciiSlugger())->slug($this->name);
    }

    /**
     * @return $this|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param Folder|null $parent
     * @return $this
     */
    public function setParent(?Folder $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Return true if the folder is the root folder of the app
     * @return bool
     */
    public function isRootFolderApp(): bool
    {
        return is_null($this->getParent());
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * Return true if $this has $folder in children of children etc...
     * @param Folder $folder
     * @return bool
     */
    public function hasThisChild(Folder $folder): bool
    {
        if ($this === $folder) {
            return true;
        }
        foreach ($this->getChildren() as $child) {
            if ($child === $folder) {
                return true;
            }

            if (!$child->getChildren()->isEmpty()) {
                if(!$child->hasThisChild($folder)) {
                    continue;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @return Collection|Doi[]
    */
    public function getDois(bool $withDeleted = true): Collection
    {
        if ($withDeleted) {
            return $this->dois;
        }

        return $this->dois->filter(function (Doi $doi) {
           return !$doi->isDeleted();
        });
    }

    public function getDoisChildren(?Folder $folder = null, ?array $dois = null): array
    {
        if (!$dois) {
            $dois = [];
        }
        if (!$folder) {
            $folder = $this;
        }
        $folder->getDois();
        foreach ($folder->getChildren() as $child) {
            if ($child->getChildren()) {
                $dois = array_merge($dois, $folder->getDoisChildren($child, $dois));
            } else {
                $dois = array_merge($dois, $child->getDois()->toArray());
            }
        }
        return array_merge($dois, $folder->getDois()->toArray());
    }

    /**
     * @param Doi $dois
     * @return $this
     */
    public function addDois(Doi $dois): self
    {
        if (!$this->dois->contains($dois)) {
            $this->dois[] = $dois;
            $dois->setFolder($this);
        }
        return $this;
    }

    /**
     * @param Doi $dois
     * @return $this
     */
    public function removeDois(Doi $dois): self
    {
        if ($this->dois->contains($dois)) {
            $this->dois->removeElement($dois);
            // set the owning side to null (unless already changed)
            if ($dois->getFolder() === $this) {
                $dois->setFolder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getOwners(): Collection
    {
        if ($parent = $this->getParent()) {
            $this->setOwners(new ArrayCollection(array_merge($this->owners->toArray(), $parent->getOwners()->toArray())));
        }
        return $this->owners;
    }

    public function addOwner(User $user): self
    {

        $this->owners->add($user);
        return $this;
    }

    /**
     * @param Collection $owners
     * @return $this
     */
    public function setOwners(Collection $owners): self
    {
        $this->owners = $owners;
        return $this;
    }

    /**
     * @param User $owner
     * @return $this
     */
    public function removeOwner(User $owner): self
    {
        if ($this->owners->contains($owner)) {
            $this->owners->removeElement($owner);
        }
        return $this;
    }
}
