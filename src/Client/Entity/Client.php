<?php

declare(strict_types=1);

namespace App\Client\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Common\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: "`client`")]
class Client extends User
{
    #[ORM\Column(type: "string", nullable: true)]
    #[Groups(['client:read'])]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: "string", nullable: true)]
    #[Groups(['client:read'])]
    private ?string $address = null;

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $phoneNumber
     * @return Client
     */
    public function setPhoneNumber(?string $phoneNumber = null): Client
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @param string|null $address
     * @return Client
     */
    public function setAddress(?string $address = null): Client
    {
        $this->address = $address;
        return $this;
    }
}
