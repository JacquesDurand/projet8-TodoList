<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Controller\UserController;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserControllerTest extends AuthenticatedWebTestCase
{

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function tearDown()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client
            ->getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ;

        $testUsers = $em
            ->getRepository(User::class)
            ->findBy(['username' => 'new User'])
            ;

        $modifiedUsers = $em
            ->getRepository(User::class)
            ->findBy(['username' => 'edited User'])
            ;

        $testUsers = array_merge($testUsers, $modifiedUsers);

        foreach ($testUsers as $user) {
            $em->remove($user);
        }
        $em->flush();
    }

    public function testListAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/users');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Liste des utilisateurs', $crawler->filter('h1')->html());

    }

    public function testGetCreateAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/users/create');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Ajouter', $crawler->filter('.btn.btn-success.pull-right')->text());
    }

    public function testCreateAction()
    {
        $this->createUser($this->client);

        $responseCrawler = $this->client->followRedirect();

        $user = $this->client
            ->getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'new User'])
            ;

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertNotNull($user);
        $this->assertTrue(in_array('ROLE_USER', $user->getRoles()));

    }

    public function testEditAction()
    {
        $this->createUser($this->client);

        /** @var EntityManagerInterface $em */
        $em = $this->client
            ->getContainer()
            ->get('doctrine.orm.default_entity_manager')
        ;

        $user = $em
            ->getRepository(User::class)
            ->findOneBy(
                ['username' => 'new User']
            )
        ;

        $crawler = $this->client->request(Request::METHOD_GET, '/users/'.$user->getId().'/edit');

        $this->editUser($this->client, $crawler);

        $responseCrawler = $this->client->followRedirect();

        /** @var User $editedUser */
        $editedUser = $em
            ->getRepository(User::class)
            ->findOneBy(
                ['username' => 'edited User']
            )
        ;
        /** @var UserPasswordEncoderInterface $passwordDecoder */
        $passwordDecoder = $this->client
            ->getContainer()
            ->get('security.password_encoder')
            ;

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertNotNull($editedUser);
        $this->assertSame('editedTest@test.com', $editedUser->getEmail());
        $this->assertTrue( $passwordDecoder->isPasswordValid($editedUser, 'editedP@ssword'));

    }

    private function createUser(Client $client)
    {
        $body = [
            'user[username]' => 'new User',
            'user[password][first]' => 'p@ssword',
            'user[password][second]' => 'p@ssword',
            'user[email]' => 'test@test.com',
        ];

        $crawler = $this->client->request(Request::METHOD_GET, '/users/create');

        $button = $crawler->filter('.btn.btn-success.pull-right');
        $form = $button->form();

        $client->submit($form, $body);
    }

    private function editUser(Client $client, ?Crawler $crawler): void
    {
        $body = [
            'user[username]' => 'edited User',
            'user[password][first]' => 'editedP@ssword',
            'user[password][second]' => 'editedP@ssword',
            'user[email]' => 'editedTest@test.com',
        ];

        $button = $crawler->filter('.btn.btn-success.pull-right');
        $form = $button->form();

        $client->submit($form, $body);
    }
}
