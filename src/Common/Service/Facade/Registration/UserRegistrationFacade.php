<?php

declare(strict_types=1);

namespace App\Common\Service\Facade\Registration;

use App\Admin\Entity\Admin;
use App\Client\Entity\Client;
use App\Common\DTO\UserDTOInterface;
use App\Common\Entity\User;
use App\Common\Security\RolesInterface;
use App\Common\Service\Country\CountryServiceInterface;
use App\Salesman\Entity\Salesman;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserRegistrationFacade implements UserRegistrationFacadeInterface
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly CountryServiceInterface     $countryService
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function registerUser(UserDTOInterface $user): UserInterface
    {
        $userEntity = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $user->email,
        ]);

        if ($userEntity) {
            throw new BadRequestHttpException('User with such email is already exists');
        }

        if ($user->type === UserDTOInterface::TYPE_CLIENT) {
            $userEntity = new Client();
            $userEntity->addRole(RolesInterface::ROLE_CLIENT);
        } elseif ($user->type === UserDTOInterface::TYPE_SALESMAN) {
            $userEntity = new Salesman();
            $userEntity->addRole(RolesInterface::ROLE_SALESMAN);
        } elseif ($user->type === UserDTOInterface::TYPE_ADMIN) {
            $userEntity = new Admin();
            $userEntity->addRole(RolesInterface::ROLE_ADMIN);
        }

        if ($userEntity === null) {
            throw new BadRequestException();
        }

        $userEntity
            ->setEmail($user->email)
            ->setTaxNumber($this->countryService->creatingTaxNumber($user->country));

        // Setting user password
        if ($user->password !== null) {
            $userEntity->setPassword($this->passwordHasher->hashPassword($userEntity, $user->password));
            $userEntity->eraseCredentials();
        }

        $this->entityManager->persist($userEntity);
        $this->entityManager->flush();

        // Send confirmation email

        return $userEntity;
    }
}
