<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InsertAnonymousUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        string $name = null
    ) {
        parent::__construct($name);
    }

    public function configure()
    {
        $this->setName('app:migrate:anon')
            ->setDescription('Adds an anonymous User to the DB for anonymous tasks')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        if ($this->entityManager->getRepository(User::class)->findOneBy(['username' => User::ANONYMOUS_USERNAME])) {
            $style->info('Anonymous User already exists, no creation needed');

            return Command::SUCCESS;
        }

        $anon = new User();
        $anon->setEmail('anon@todolist.com');
        $anon->setUsername(User::ANONYMOUS_USERNAME);
        $anon->setPassword($this->passwordHasher->hashPassword($anon, User::ANONYMOUS_PASSWORD));

        $this->entityManager->persist($anon);
        $this->entityManager->flush();

        $style->info('Anonymous User successfully created');

        return Command::SUCCESS;
    }
}
