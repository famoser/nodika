<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\AppBundle\Traits;

use App\Entity\FrontendUser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 14:13.
 */
class UserTraitTest extends WebTestCase
{
    public function testPasswordHash()
    {
        $user = new FrontendUser();
        $user->setPlainPassword('asdf1234');
        $user->setPassword();
        $this->assertEmpty($user->getPlainPassword());
        $this->assertNotEmpty('' !== $user->getPassword());
    }
}
