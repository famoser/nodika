<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 14:32
 */

namespace AppBundle\Form\Generic;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class SetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => $options["translation_domain"]];


        $builder->add("plainPassword", PasswordType::class, ["translation_domain" => "user_trait"]);
        $builder->add("repeatPlainPassword", PasswordType::class, ["translation_domain" => "user_trait"]);

        $builder->add("set password", SubmitType::class, $transArray);
    }
}