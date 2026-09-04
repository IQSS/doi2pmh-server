<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[\Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->userRepository->findOneBy(['email' => $identifier]);
    }

    #[\Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->userRepository->findOneBy(['email' => $user->getUserIdentifier()]);
    }

    #[\Override]
    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
