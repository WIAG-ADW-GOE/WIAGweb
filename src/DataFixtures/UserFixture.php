<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends BaseFixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder) {
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(4, 'query_users', function($i) {
            $user = new User();
            switch($i) {
            case 0:
                $user->setEmail('bkroege@gwdg.de');
                $user->setPassword($this->passwordEncoder->encodePassword($user, 'geheim'));
                $user->setRoles(['ROLE_QUERY']);
                break;
            case 1:
                $user->setEmail('cpopp@gwdg.de');
                $user->setPassword($this->passwordEncoder->encodePassword($user, 'geheim'));
                $user->setRoles(['ROLE_QUERY']);
                break;
            case 2:
                $user->setEmail('ghertko@gwdg.de');
                $user->setPassword($this->passwordEncoder->encodePassword($user, 'streng geheim'));
                $user->setRoles(['ROLE_QUERY', 'ROLE_ADMIN']);
                break;
            case 3:
                $user->setEmail('wiag-guest@adw-goe.de');
                $user->setPassword($this->passwordEncoder->encodePassword($user, 'E13'));
                $user->setRoles(['ROLE_QUERY']);
                break;
            }
            return $user;
        });

        $manager->flush();
    }
}
