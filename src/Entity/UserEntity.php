<?php

namespace App\Entity;

use App\Repository\UserEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserEntityRepository::class)]
#[ORM\Table(name: "`user`")]
#[UniqueEntity(fields: ['email', 'username'], message: 'The email or username is already in use.')]
class UserEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['user'])]
    private UuidInterface $id;

    #[ORM\Column(type: 'string')]
    #[Groups(['user'])]
    private ?string $password;
    
    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(['user'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(['user'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'json', nullable: false)]
    #[Groups(['user'])]
    private ?array $roles = [];

    public function __construct(
        #[ORM\Column(type: 'string', length: 255, unique: true)]
        #[Groups(['user', 'model.owner'])]
        private ?string $email,
        #[ORM\Column(type: 'string', length: 255, nullable: false)]
        #[Groups(['user'])]
        private ?string $username,
    ) {
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /** @see PasswordAuthenticatedUserInterface */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /** @see UserInterface */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
