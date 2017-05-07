<?php

namespace Tests\AppBundle\Traits;

use AppBundle\Entity\FrontendUser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 14:13
 */
class UserTraitTest extends WebTestCase
{
    public function testPasswordHash()
    {
        $user = new FrontendUser();
        $user->setPlainPassword("asdf1234");
        $this->assertTrue($user->isValidPlainPassword());

        $user->hashAndRemovePlainPassword();
        $this->assertEmpty($user->getPlainPassword());
        $this->assertNotEmpty($user->getPasswordHash() != "");

        $user->setPlainPassword("asdf1234");
        $this->assertTrue($user->tryLoginWithPlainPassword());
    }
}