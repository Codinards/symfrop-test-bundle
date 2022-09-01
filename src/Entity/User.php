<?php

namespace Njeaner\Symfrop\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Njeaner\Symfrop\Entity\Contract\ActionInterface;
use Njeaner\Symfrop\Entity\Contract\RoleInterface;
use Njeaner\Symfrop\Entity\Contract\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private ?int $id = null;

    private ?string $pseudo = null;

    private array $roles = [];

    private ?string $password = null;

    private ?string $username = null;

    private ?string $address = null;

    private ?string $phoneNumber = null;

    private ?string $image = null;

    private ?\DateTimeImmutable $createdAt = null;

    private ?\DateTimeImmutable $updatedAt = null;

    private ?bool $isLocked = false;

    private ?\DateTimeImmutable $lockedAt = null;

    private ?self $admin = null;

    private ?self $parrain = null;

    private Collection $parraineds;

    private ?Role $role = null;

    public function __construct()
    {
        $this->parraineds = new ArrayCollection();
        $this->setCreatedAt(new DateTimeImmutable());
        $this->setUpdatedAt($this->getCreatedAt());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->pseudo;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isIsLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    public function getLockedAt(): ?\DateTimeImmutable
    {
        return $this->lockedAt;
    }

    public function setLockedAt(?\DateTimeImmutable $lockedAt): self
    {
        $this->lockedAt = $lockedAt;

        return $this;
    }

    public function getAdmin(): ?self
    {
        return $this->admin;
    }

    public function setAdmin(?self $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getParrain(): ?self
    {
        return $this->parrain;
    }

    public function setParrain(?self $parrain): self
    {
        $this->parrain = $parrain;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getParraineds(): Collection
    {
        return $this->parraineds;
    }

    public function addParrained(self $parrained): self
    {
        if (!$this->parraineds->contains($parrained)) {
            $this->parraineds[] = $parrained;
            $parrained->setParrain($this);
        }

        return $this;
    }

    public function removeParrained(self $parrained): self
    {
        if ($this->parraineds->removeElement($parrained)) {
            // set the owning side to null (unless already changed)
            if ($parrained->getParrain() === $this) {
                $parrained->setParrain(null);
            }
        }

        return $this;
    }

    public function getRole(): ?RoleInterface
    {
        return $this->role;
    }

    public function setRole(?RoleInterface $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getActions(): array
    {
        return $this->getRole()->getActions();
    }

    public function hasAction(ActionInterface $action): bool
    {
        return $this->getRole()->hasAction($action);
    }

    public function __toString(): string
    {
        return $this->username;
    }
}
