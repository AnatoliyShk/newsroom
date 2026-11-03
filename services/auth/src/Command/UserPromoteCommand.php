<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:user:promote', description: 'Gives a user ROLE_EDITOR')]
final class UserPromoteCommand
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Email of the user')] string $email,
        #[Argument('Role to give', suggestedValues: User::ASSIGNABLE_ROLES)] string $role): int
    {
        if (!in_array($role, User::ASSIGNABLE_ROLES, true)) {
            $io->error(sprintf('Unknown role "%s". Use one of: %s.', $role, implode(', ', User::ASSIGNABLE_ROLES)));

            return Command::INVALID;
        }

        $user = $this->userRepository->findOneBy(['email' => mb_strtolower($email)]);

        if (null === $user) {
            $io->error(sprintf('No user with email "%s".', $email));

            return Command::FAILURE;
        }

        $user->addRole($role);
        $this->entityManager->flush();

        $io->success(sprintf('%s now has %s.', $user->getEmail(), $role));

        return Command::SUCCESS;
    }
}
