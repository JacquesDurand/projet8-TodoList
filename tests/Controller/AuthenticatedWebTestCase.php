<?php

namespace Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;

abstract class AuthenticatedWebTestCase extends WebTestCase
{
    protected function createAuthenticatedClient(array $roles = null): KernelBrowser
    {
        // Assign default user roles if no roles have been passed.
        if($roles == null) {
            $roles = ['ROLE_SUPER_ADMIN'];
        } else {
            $tmpRoles = [];
            foreach($roles as $role)
            {
                $tmpRoles[] = $role;
            }
            $roles = $tmpRoles;
        }

        $client = static::createClient();
        /** @var User $user */
        $user = $client
            ->getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin'])
            ;

        return self::createAuthentication($client, $user);
    }

    private static function createAuthentication(KernelBrowser $client, User $user): KernelBrowser
    {
        // Read below regarding config_test.yml!
        $session = $client->getContainer()->get('session');

        // Authenticate
        $firewall = 'main'; // This  MUST MATCH the name in your security.firewalls.->user_area<-
        $token = new UsernamePasswordToken($user, null, $firewall, ['ROLE_USER']);
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        // Save authentication
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }

}