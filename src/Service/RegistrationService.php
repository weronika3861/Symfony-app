<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ORMException
     */
    public function register(User $user, UserPasswordHasherInterface $passwordHasher, ?string $encodedPassword): void
    {
        $user->setPassword($passwordHasher->hashPassword($user, $encodedPassword));

        $this->userRepository->save($user);
    }
}
