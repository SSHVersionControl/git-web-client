<?php

declare(strict_types=1);

namespace VersionControl\GitControlBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use VersionControl\GitCommandBundle\GitCommands\Command\GitUserInterface;
use VersionControl\GitControlBundle\Entity\User\User;

final class GitUserFactory implements GitUserInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * GitUserFactory constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getUser()->getName();
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getUser()->getEmail();
    }

    /**
     * @return User
     */
    private function getUser(): User
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            throw new RuntimeException('No user appears to be logged in');
        }

        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof User) {
            throw new RuntimeException(sprintf('user must be an instance of %s', User::class));
        }

        return $user;
    }
}
