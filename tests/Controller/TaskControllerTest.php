<?php

namespace Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
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

    public function testTaskListNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/tasks');

        $crawler = $client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Se connecter', $crawler->filter('form .btn')->html());
    }

    public function testTaskListLoggedIn(): void
    {
        $client = static::createClient();

        $this->loginFirstUser($client);

        $crawler = $client->request(Request::METHOD_GET, '/tasks');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Créer une tâche', $crawler->filter('.btn.btn-info.pull-right')->text());
    }

    public function testGetTaskCreate(): void
    {
        $client = static::createClient();

        $this->loginFirstUser($client);

        $crawler = $client->request(Request::METHOD_GET, '/tasks/create');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Ajouter', $crawler->filter('.btn.btn-success.pull-right')->text());
    }

    public function testCreateTask(): void
    {
        $client = static::createClient();

        $this->loginFirstUser($client);

        $this->createTask($client);

        $responseCrawler = $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var ?Task $task */
        $task = $entityManager
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
            ;

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('new Task content', $responseCrawler->filter('.caption p')->text());
        $this->assertNotNull($task);
        $this->assertNotNull($task->getCreatedAt());
        $this->assertEquals('admin', $task->getUser()->getUsername());
    }

    public function testEditTask(): void
    {
        $client = static::createClient();

        $this->loginFirstUser($client);

        $this->createTask($client);

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ;

        /** @var Task $task */
        $task = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
        ;

        $crawler = $client->request(Request::METHOD_GET, '/tasks/'.$task->getId().'/edit');

        $this->editTask($client, $crawler);

        $responseCrawler = $client->followRedirect();

        $editedTask = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'edited Task']
            )
        ;

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('edited Task content', $responseCrawler->filter('.caption p')->text());
        $this->assertNotNull($editedTask);
    }

    public function testToggleTask(): void
    {
        $client = static::createClient();

        $this->loginFirstUser($client);

        $this->createTask($client);

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
        ;

        /** @var Task $task */
        $task = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
        ;

        $client->request(Request::METHOD_GET, '/tasks/'.$task->getId().'/toggle');

        $responseCrawler = $client->followRedirect();

        /** @var Task $toggledTask */
        $toggledTask = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
        ;

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('new Task content', $responseCrawler->filter('.caption p')->text());
        $this->assertTrue($toggledTask->isDone());
    }

    public function testDeleteTask(): void
    {
        $client = static::createClient();

        $this->loginFirstUser($client);

        $this->createTask($client);

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
        ;

        /** @var Task $task */
        $task = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
        ;

        $client->request(Request::METHOD_GET, '/tasks/'.$task->getId().'/delete');

        $responseCrawler = $client->followRedirect();

        $deletedTask = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
        ;

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEmpty($responseCrawler->filter('.caption p'));
        $this->assertNull($deletedTask);
    }

    public function testAnotherUserCannotDeleteTask(): void
    {
        $client = static::createClient();

        $this->loginFirstUser($client);
        $this->createTask($client);

        $this->loginSecondUser($client);

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
        ;

        /** @var Task $task */
        $task = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
        ;

        $client->request(Request::METHOD_GET, '/tasks/'.$task->getId().'/delete');
        $responseCrawler = $client->followRedirect();

        $notDeletedTask = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
        ;

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($responseCrawler->filter('.caption p'));
        $this->assertNotEmpty($responseCrawler->filter('.alert.alert-danger'));
        $this->assertNotNull($notDeletedTask);
    }

    private function createTask(KernelBrowser $client): void
    {
        $body = [
            'task[title]' => 'new Task',
            'task[content]' => 'new Task content',
        ];

        $crawler = $client->request(Request::METHOD_GET, '/tasks/create');
        $button = $crawler->filter('.btn.btn-success.pull-right');
        $form = $button->form();

        $client->submit($form, $body);
    }

    private function editTask(KernelBrowser $client, ?Crawler $crawler): void
    {
        $body = [
            'task[title]' => 'edited Task',
            'task[content]' => 'edited Task content',
        ];

        $button = $crawler->filter('.btn.btn-success.pull-right');
        $form = $button->form();

        $client->submit($form, $body);
    }

    public function loginFirstUser(KernelBrowser $client): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');

        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin']);

        $client->loginUser($user);
    }

    public function loginSecondUser(KernelBrowser $client): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');

        $secondUser = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin2']);

        $client->loginUser($secondUser);
    }
}
