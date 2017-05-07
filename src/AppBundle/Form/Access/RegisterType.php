<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:21
 */

namespace AppBundle\Form\Access;


use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Person;
use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\PersonTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "access"];
        $inheritArray = ['inherit_data' => true];
        //add person fields
        $builder->add(
            PersonTrait::getPersonBuilder(
                $builder->create('person', FormType::class, $inheritArray + $transArray),
                ["translation_domain" => "person"]
            )
        );

        //add address fields
        $builder->add(
            AddressTrait::getAddressBuilder(
                $builder->create('address', FormType::class, $inheritArray + $transArray),
                ["translation_domain" => "address"]
            )
        );

        //add communication fields
        $builder->add(
            CommunicationTrait::getCommunicationBuilder(
                $builder->create('communication', FormType::class, $inheritArray +$transArray),
                ["translation_domain" => "communication"]
            )
        );


        $builder->add("registrieren", SubmitType::class, $transArray);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Person::class,
        ));
    }
}