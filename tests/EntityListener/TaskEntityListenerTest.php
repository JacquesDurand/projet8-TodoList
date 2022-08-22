<?php

namespace Tests\EntityListener;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskEntityListenerTest extends KernelTestCase
{
    public function tearDown(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');

        $tasks = $entityManager
            ->getRepository(Task::class)
            ->findAll()
        ;
        foreach ($tasks as $task) {
            $entityManager->remove($task);
        }
        $entityManager->flush();

        parent::tearDown();
    }

    public function testPrePersist(): void
    {
        $task = new Task();
        $task->setContent('test');
        $task->setTitle('test');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');

        $entityManager->persist($task);
        $entityManager->flush();

        $task = $entityManager
            ->getRepository(Task::class)
            ->findOneBy(['title' => 'test'])
            ;

        $this->assertNotNull($task->getUser());
        $this->assertEquals(User::ANONYMOUS_USERNAME, $task->getUser()->getUsername());
    }

    public function testPrePersistWithoutAnon(): void
    {
        $task = new Task();
        $task->setContent('test');
        $task->setTitle('test');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');

        $anon = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => User::ANONYMOUS_USERNAME])
            ;

        $entityManager->remove($anon);
        $entityManager->flush();

        $entityManager->persist($task);
        $entityManager->flush();

        $task = $entityManager
            ->getRepository(Task::class)
            ->findOneBy(['title' => 'test'])
        ;

        $this->assertNotNull($task->getUser());
        $this->assertEquals(User::ANONYMOUS_USERNAME, $task->getUser()->getUsername());
    }
}
