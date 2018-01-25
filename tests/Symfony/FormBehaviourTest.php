<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Symfony;

use App\Entity\Member;
use App\Form\Member\MemberType;
use Symfony\Component\Form\Test\TypeTestCase;

class FormBehaviourTest extends TypeTestCase
{
    public function testObjectSet()
    {
        $form = $this->factory->create(MemberType::class);

        $object = new Member();
        $object->setName('custom name');
        $object->setEmail('org@email.ch');
        $form->setData($object);

        $form->submit(['new_member[thing][name]' => 'my name', 'email' => 'me@mail.com']);
        $this->assertTrue($form->getData() instanceof Member);
    }
}
