<?php

namespace Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AuthenticatedWebTestCase extends WebTestCase
{
    protected function createAuthenticatedClientForAnotherUser(KernelBrowser $client): KernelBrowser
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var User $user */
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin2'])
        ;

        $roles = $user->getRoles();

        return self::createAuthentication($client, $user, $roles);
    }

    protected function createAuthenticatedClient(KernelBrowser $client): KernelBrowser
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var User $user */
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin'])
            ;

        $roles = $user->getRoles();

        return self::createAuthentication($client, $user, $roles);
    }

    /**
     * @param array<string>|null $roles
     */
    private static function createAuthentication(KernelBrowser $client, User $user, array $roles = null): KernelBrowser
    {
        /** @var Session $session */
        $session = static::getContainer()->get('session');

        // Authenticate
        $firewall = 'main'; // This  MUST MATCH the name in your security.firewalls.->user_area<-
        $token = new UsernamePasswordToken($user, $firewall, $roles);
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        // Save authentication
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }
}
