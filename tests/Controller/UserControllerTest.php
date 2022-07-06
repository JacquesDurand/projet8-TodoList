<?php

namespace Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    public function tearDown(): void
    {
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()
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

        parent::tearDown();
    }

    public function testListActionNotAdmin()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/users');

        $crawler = $client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Se connecter', $crawler->filter('form .btn')->html());
    }

    public function testListActionLoggedIn()
    {
        $client = static::createClient();
        $this->loginFirstUser($client);
        $crawler = $client->request(Request::METHOD_GET, '/users');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Liste des utilisateurs', $crawler->filter('h1')->html());
    }

    public function testGetCreateActionLoggedIn()
    {
        $client = static::createClient();
        $this->loginFirstUser($client);

        $crawler = $client->request(Request::METHOD_GET, '/users/create');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Ajouter', $crawler->filter('.btn.btn-success.pull-right')->text());
        $this->assertStringContainsString('Nom d\'utilisateur', $crawler->filter('#user>div>label')->text());
    }

    public function testCreateAction()
    {
        $client = static::createClient();
        $this->loginFirstUser($client);

        $this->createUser($client);

        $client->followRedirect();

        $user = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'new User'])
            ;

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotNull($user);
        $this->assertTrue(in_array('ROLE_USER', $user->getRoles()));
        $this->assertTrue(in_array('ROLE_ADMIN', $user->getRoles()));
    }

    public function testEditAction()
    {
        $client = static::createClient();
        $this->loginFirstUser($client);

        $this->createUser($client);

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
        ;

        $user = $em
            ->getRepository(User::class)
            ->findOneBy(
                ['username' => 'new User']
            )
        ;

        $crawler = $client->request(Request::METHOD_GET, '/users/'.$user->getId().'/edit');

        $this->editUser($client, $crawler);

        $client->followRedirect();

        /** @var User $editedUser */
        $editedUser = $em
            ->getRepository(User::class)
            ->findOneBy(
                ['username' => 'edited User']
            )
        ;
        /** @var UserPasswordHasherInterface $passwordDecoder */
        $passwordDecoder = static::getContainer()
            ->get('security.user_password_hasher')
            ;

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotNull($editedUser);
        $this->assertSame('editedTest@test.com', $editedUser->getEmail());
        $this->assertTrue($passwordDecoder->isPasswordValid($editedUser, 'editedP@ssword'));
        $this->assertFalse(in_array('ROLE_ADMIN', $editedUser->getRoles()));
    }

    private function createUser(KernelBrowser $client)
    {
        $body = [
            'user[username]' => 'new User',
            'user[password][first]' => 'p@ssword',
            'user[password][second]' => 'p@ssword',
            'user[email]' => 'test@test.com',
            'user[roles]' => ['ROLE_USER', 'ROLE_ADMIN'],
        ];

        $crawler = $client->request(Request::METHOD_GET, '/users/create');

        $button = $crawler->filter('.btn.btn-success.pull-right');
        $form = $button->form();

        $client->submit($form, $body);
    }

    private function editUser(KernelBrowser $client, ?Crawler $crawler): void
    {
        $body = [
            'user[username]' => 'edited User',
            'user[password][first]' => 'editedP@ssword',
            'user[password][second]' => 'editedP@ssword',
            'user[email]' => 'editedTest@test.com',
            'user[roles][0]' => 'ROLE_USER',
            'user[roles][1]' => false,
        ];

        $button = $crawler->filter('.btn.btn-success.pull-right');
        $form = $button->form();

        $client->submit($form, $body);
    }

    /**
     * @param KernelBrowser $client
     * @return void
     */
    public function loginFirstUser(KernelBrowser $client): void
    {
        $user = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin']);

        $client->loginUser($user);
    }
}
