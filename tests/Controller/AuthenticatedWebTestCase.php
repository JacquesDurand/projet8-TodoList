<?php

namespace Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AuthenticatedWebTestCase extends WebTestCase
{
    protected function createAuthenticatedClient(KernelBrowser $client, array $roles = null): KernelBrowser
    {
        /** @var User $user */
        $user = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin'])
            ;

        return self::createAuthentication($client, $user, $roles);
    }

    private static function createAuthentication(KernelBrowser $client, User $user, array $roles = null): KernelBrowser
    {
        // Read below regarding config_test.yml!
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
