<?php

namespace Tests\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends AuthenticatedWebTestCase
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

    public function testTaskListNotLoggedIn()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/tasks');

        $crawler = $client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Se connecter', $crawler->filter('form .btn')->html());
    }

    public function testTaskListLoggedIn()
    {
        $client = static::createClient();

        $authorizedClient = $this->createAuthenticatedClient($client);

        $crawler = $authorizedClient->request(Request::METHOD_GET, '/tasks');

        $this->assertEquals(Response::HTTP_OK, $authorizedClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('Créer une tâche', $crawler->filter('.btn.btn-info.pull-right')->text());
    }

    public function testGetTaskCreate()
    {
        $client = static::createClient();

        $authorizedClient = $this->createAuthenticatedClient($client);

        $crawler = $authorizedClient->request(Request::METHOD_GET, '/tasks/create');

        $this->assertEquals(Response::HTTP_OK, $authorizedClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('Ajouter', $crawler->filter('.btn.btn-success.pull-right')->text());
    }

    public function testCreateTask()
    {
        $client = static::createClient();

        $authorizedClient = $this->createAuthenticatedClient($client);

        $this->createTask($authorizedClient);

        $responseCrawler = $authorizedClient->followRedirect();

        /** @var ?Task $task */
        $task = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
            ;

        $this->assertEquals(Response::HTTP_OK, $authorizedClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('new Task content', $responseCrawler->filter('.caption p')->text());
        $this->assertNotNull($task);
        $this->assertNotNull($task->getCreatedAt());
        $this->assertEquals('admin', $task->getUser()->getUsername());
    }

    public function testEditTask()
    {
        $client = static::createClient();

        $authorizedClient = $this->createAuthenticatedClient($client);
        $this->createTask($authorizedClient);

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

        $crawler = $authorizedClient->request(Request::METHOD_GET, '/tasks/'.$task->getId().'/edit');

        $this->editTask($authorizedClient, $crawler);

        $responseCrawler = $authorizedClient->followRedirect();

        $editedTask = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'edited Task']
            )
        ;

        $this->assertEquals(Response::HTTP_OK, $authorizedClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('edited Task content', $responseCrawler->filter('.caption p')->text());
        $this->assertNotNull($editedTask);
    }

    public function testToggleTask()
    {
        $client = static::createClient();

        $authorizedClient = $this->createAuthenticatedClient($client);
        $this->createTask($authorizedClient);

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

        $authorizedClient->request(Request::METHOD_GET, '/tasks/'.$task->getId().'/toggle');

        $responseCrawler = $authorizedClient->followRedirect();

        /** @var Task $toggledTask */
        $toggledTask = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
        ;

        $this->assertEquals(Response::HTTP_OK, $authorizedClient->getResponse()->getStatusCode());
        $this->assertStringContainsString('new Task content', $responseCrawler->filter('.caption p')->text());
        $this->assertTrue($toggledTask->isDone());
    }

    public function testDeleteTask()
    {
        $client = static::createClient();

        $authorizedClient = $this->createAuthenticatedClient($client);
        $this->createTask($authorizedClient);

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

        $authorizedClient->request(Request::METHOD_GET, '/tasks/'.$task->getId().'/delete');

        $responseCrawler = $authorizedClient->followRedirect();

        $deletedTask = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
        ;

        $this->assertEquals(Response::HTTP_OK, $authorizedClient->getResponse()->getStatusCode());
        $this->assertEmpty($responseCrawler->filter('.caption p'));
        $this->assertNull($deletedTask);
    }

    public function testAnotherUserCannotDeleteTask()
    {
        $client = static::createClient();

        $authorizedClient = $this->createAuthenticatedClient($client);

        $this->createTask($authorizedClient);

        $newUserClient = $this->createAuthenticatedClientForAnotherUser($authorizedClient);

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

        $newUserClient->request(Request::METHOD_GET, '/tasks/'.$task->getId().'/delete');
        $responseCrawler = $newUserClient->followRedirect();

        $notDeletedTask = $em
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
        ;

        $this->assertEquals(Response::HTTP_OK, $authorizedClient->getResponse()->getStatusCode());
        $this->assertNotEmpty($responseCrawler->filter('.caption p'));
        $this->assertNotEmpty($responseCrawler->filter('.alert.alert-danger'));
        $this->assertNotNull($notDeletedTask);
    }

    private function createTask(KernelBrowser $authorizedClient): void
    {
        $body = [
            'task[title]' => 'new Task',
            'task[content]' => 'new Task content',
        ];

        $crawler = $authorizedClient->request(Request::METHOD_GET, '/tasks/create');
        $button = $crawler->filter('.btn.btn-success.pull-right');
        $form = $button->form();

        $authorizedClient->submit($form, $body);
    }

    private function editTask(KernelBrowser $authorizedClient, ?Crawler $crawler): void
    {
        $body = [
            'task[title]' => 'edited Task',
            'task[content]' => 'edited Task content',
        ];

        $button = $crawler->filter('.btn.btn-success.pull-right');
        $form = $button->form();

        $authorizedClient->submit($form, $body);
    }
}
