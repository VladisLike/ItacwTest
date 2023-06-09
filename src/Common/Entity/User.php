<?php

declare(strict_types=1);

namespace App\Common\Entity;

use App\Admin\Entity\Admin;
use App\Client\Entity\Client;
use App\Salesman\Entity\Salesman;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity, ORM\Table(name: "`user`"), UniqueEntity(fields: ["email"])]
#[ORM\InheritanceType("JOINED"), ORM\DiscriminatorColumn(name: "user_type", type: "string")]
#[ORM\DiscriminatorMap(['user' => User::class, 'admin' => Admin::class, 'client' => Client::class, 'salesman' => Salesman::class])]
class User extends AbstractEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: "string", unique: true)]
    private string $email;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    public function __toString(): string
    {
        return $this->email;
    }

    /**
     * @param string $role
     * @return $this
     */
    public function addRole(string $role): self
    {
        if (\in_array($role, $this->roles, true) === false) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @param string $role
     * @return $this
     */
    public function removeRole(string $role): self
    {
        if (\in_array($role, $this->roles, true)) {
            unset($this->roles[$role]);
        }

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return \in_array($role, $this->roles, true);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string|null $plainPassword
     * @return $this
     */
    public function setPlainPassword(?string $plainPassword = null): User
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return void
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     * @return User
     */
    public function setPassword(?string $password = null): User
    {
        $this->password = $password;
        return $this;
    }
}
