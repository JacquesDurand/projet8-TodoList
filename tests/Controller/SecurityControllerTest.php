<?php

namespace Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request(Request::METHOD_GET, '/');

        $crawler = $this->client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Se connecter', $crawler->filter('form .btn')->html());
    }

    public function testLoginCheck(): void
    {
        $this->client->request(Request::METHOD_GET, '/login_check');

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client->getResponse()->getStatusCode());
    }

    public function testLogout(): void
    {
        $this->client->request(Request::METHOD_GET, '/logout');

        $this->client->followRedirect(); // Goes to '/'
        $crawler = $this->client->followRedirect(); // Then to '/login'

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Se connecter', $crawler->filter('form .btn')->html());
    }

    public function testAccessDeniedRedirect(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');

        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'random']);

        $this->client->loginUser($user);

        $this->client->request(Request::METHOD_GET, '/users');

        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
    }
}
