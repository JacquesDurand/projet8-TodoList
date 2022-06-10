<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\AuthenticatedWebTestCase;

class TaskControllerTest extends AuthenticatedWebTestCase
{

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function tearDown()
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::createClient()
            ->getContainer()
            ->get('doctrine.orm.default_entity_manager');

        $tasks = $entityManager
            ->getRepository(Task::class)
            ->findAll()
            ;
        foreach ($tasks as $task) {
            $entityManager->remove($task);
        }
        $entityManager->flush();
    }


    public function testTaskListNotLoggedIn()
    {
        $this->client->request(Request::METHOD_GET, '/tasks');

        $crawler = $this->client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Se connecter', $crawler->filter('form .btn')->html());
    }

    public function testTaskListLoggedIn()
    {
        $authorizedClient = $this->createAuthenticatedClient(['ROLE_USER']);

        $crawler = $authorizedClient->request(Request::METHOD_GET, '/tasks');

        $this->assertEquals(Response::HTTP_OK, $authorizedClient->getResponse()->getStatusCode());
        $this->assertContains('Créer une tâche', $crawler->filter('.btn.btn-info.pull-right')->text());

    }

    public function testGetTaskCreate()
    {
        $authorizedClient = $this->createAuthenticatedClient(['ROLE_USER']);

        $crawler = $authorizedClient->request(Request::METHOD_GET, '/tasks/create');

        $this->assertEquals(Response::HTTP_OK, $authorizedClient->getResponse()->getStatusCode());
        $this->assertContains('Ajouter', $crawler->filter('.btn.btn-success.pull-right')->text());
    }

    public function testCreateTask()
    {
        $authorizedClient = $this->createAuthenticatedClient(['ROLE_USER']);

        $this->createTask($authorizedClient);

        $responseCrawler = $authorizedClient->followRedirect();

        /** @var ?Task $task */
        $task = $authorizedClient
            ->getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(Task::class)
            ->findOneBy(
                ['title' => 'new Task']
            )
            ;

        $this->assertEquals(Response::HTTP_OK, $authorizedClient->getResponse()->getStatusCode());
        $this->assertContains('new Task content', $responseCrawler->filter('.caption p')->text());
        $this->assertNotNull($task);
        $this->assertNotNull($task->getCreatedAt());
        $this->assertEquals('admin',$task->getUser()->getUsername());

    }

    public function testEditTask()
    {
        $authorizedClient = $this->createAuthenticatedClient(['ROLE_USER']);
        $this->createTask($authorizedClient);

        /** @var EntityManagerInterface $em */
        $em = $authorizedClient
            ->getContainer()
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
        $this->assertContains('edited Task content', $responseCrawler->filter('.caption p')->text());
        $this->assertNotNull($editedTask);
    }

    public function testToggleTask()
    {
        $authorizedClient = $this->createAuthenticatedClient(['ROLE_USER']);
        $this->createTask($authorizedClient);

        /** @var EntityManagerInterface $em */
        $em = $authorizedClient
            ->getContainer()
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
        $this->assertContains('new Task content', $responseCrawler->filter('.caption p')->text());
        $this->assertTrue($toggledTask->isDone());

    }

    public function testDeleteTask()
    {
        $authorizedClient = $this->createAuthenticatedClient(['ROLE_USER']);
        $this->createTask($authorizedClient);

        /** @var EntityManagerInterface $em */
        $em = $authorizedClient
            ->getContainer()
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
        $this->assertEmpty( $responseCrawler->filter('.caption p'));
        $this->assertNull($deletedTask);
        
    }

    private function createTask(Client $authorizedClient): void
    {
        $body = [
            'task[title]' => 'new Task',
            'task[content]' => 'new Task content'
        ];

        $crawler = $authorizedClient->request(Request::METHOD_GET, '/tasks/create');
        $button = $crawler->filter('.btn.btn-success.pull-right');
        $form = $button->form();

        $authorizedClient->submit($form, $body);
    }

    private function editTask(Client $authorizedClient,?Crawler $crawler): void
    {
        $body = [
            'task[title]' => 'edited Task',
            'task[content]' => 'edited Task content'
        ];

        $button = $crawler->filter('.btn.btn-success.pull-right');
        $form = $button->form();

        $authorizedClient->submit($form, $body);

    }
}