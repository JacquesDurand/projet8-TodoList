<?php

namespace Tests\Command;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class InsertAnonymousUserCommandTest extends KernelTestCase
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

    public function testExecute(): void
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:migrate:anon');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithoutAnon(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');

        $anon = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => User::ANONYMOUS_USERNAME])
        ;

        $entityManager->remove($anon);
        $entityManager->flush();

        $kernel = static::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:migrate:anon');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }
}
