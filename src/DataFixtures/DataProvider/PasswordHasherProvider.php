<?php

namespace App\DataFixtures\DataProvider;

use App\Entity\User;
use Faker\Generator;
use Faker\Provider\Base;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordHasherProvider extends Base
{
    private UserPasswordEncoderInterface $passwordHasher;

    public function __construct(Generator $generator, UserPasswordEncoderInterface $passwordHasher)
    {
        parent::__construct($generator);
        $this->passwordHasher = $passwordHasher;
    }

    public function hashPassword(string $plainPassword): string
    {
        return $this->passwordHasher->encodePassword((new User()), $plainPassword);
    }
}
