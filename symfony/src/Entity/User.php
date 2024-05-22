<?php /** @noinspection PhpPropertyOnlyWrittenInspection */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isAdmin = false;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Folder", inversedBy="owners", cascade={"remove"})
     */
    private ?Folder $rootFolder = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $firstConnexion = false;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @param bool $isAdmin
     * @return $this
     */
    public function setIsAdmin(bool $isAdmin = false): self
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return [$this->isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER'];
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return Folder
     */
    public function getRootFolder(): ?Folder
    {
        return $this->rootFolder;
    }

    /**
     * @param Folder|null $folder
     * @return $this
     */
    public function setRootFolder(?Folder $folder): self
    {
        $this->rootFolder = $folder;
        return $this;
    }

    /**
     * @param Folder $folder
     * @return bool
     */
    public function hasRightsFor(Folder $folder): bool
    {
        return $this->isAdmin() || $this->getRootFolder()->hasThisChild($folder);
    }

    /**
     * @param Folder $folder
     * @return bool
     */
    public function canDelete(Folder $folder): bool
    {
       return $this->hasRightsFor($folder) && !$folder->isRootFolderApp();
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): void
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return bool
     */
    public function isFirstConnexion(): bool
    {
        return $this->firstConnexion;
    }

    /**
     * @param bool $firstConnexion
     */
    public function setFirstConnexion(bool $firstConnexion): void
    {
        $this->firstConnexion = $firstConnexion;
    }
}
