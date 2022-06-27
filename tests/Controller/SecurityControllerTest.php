<?php

namespace Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex()
    {
        $this->client->request(Request::METHOD_GET, '/');

        $crawler = $this->client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Se connecter', $crawler->filter('form .btn')->html());
    }

    public function testLoginCheck()
    {
        $this->client->request(Request::METHOD_GET, '/login_check');

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client->getResponse()->getStatusCode());
    }

    public function testLogout()
    {
        $this->client->request(Request::METHOD_GET, '/logout');

        $this->client->followRedirect(); // Goes to '/'
        $crawler = $this->client->followRedirect(); // Then to '/login'

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Se connecter', $crawler->filter('form .btn')->html());
    }
}
