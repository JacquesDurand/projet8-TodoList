<?php

namespace App\EntityListener;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\PrePersist;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TaskEntityListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[PrePersist]
    public function prePersist(Task $task, LifecycleEventArgs $eventArgs): void
    {
        if (null !== $task->getUser()) {
            return;
        }
        $anon = $this->getOrCreateAnonymousUser();
        $task->setUser($anon);
    }

    private function getOrCreateAnonymousUser(): User
    {
        if ($anon = $this->entityManager->getRepository(User::class)->findOneBy(['username' => User::ANONYMOUS_USERNAME])) {
            return $anon;
        }

        $anon = new User();
        $anon->setEmail('anon@todolist.com');
        $anon->setUsername(User::ANONYMOUS_USERNAME);
        $anon->setPassword($this->passwordHasher->hashPassword($anon, User::ANONYMOUS_PASSWORD));

        $this->entityManager->persist($anon);

        return $anon;
    }
}
