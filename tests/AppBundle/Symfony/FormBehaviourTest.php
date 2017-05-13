<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13/05/2017
 * Time: 14:25
 */

namespace AppBundle\Symfony;


use AppBundle\Entity\Member;
use AppBundle\Form\Member\NewMemberType;
use Symfony\Component\Form\Test\TypeTestCase;

class FormBehaviourTest extends TypeTestCase
{
    public function testObjectSet()
    {
        $form = $this->factory->create(NewMemberType::class);

        $object = new Member();
        $form->setData($object);

        $this->assertTrue($form->isSynchronized());
        $form->submit([]);
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue(count($form->getErrors()) > 0);

        $object = new Member();
        $object->setName("custom name");
        $object->setEmail("org@email.ch");
        $form->setData($object);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isSubmitted());
        $form->submit(["name" => "my name", "email" => "me@mail.com"]);
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue(count($form->getErrors()) == 0);
    }
}