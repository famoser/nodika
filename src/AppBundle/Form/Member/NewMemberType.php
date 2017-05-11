<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 19:11
 */

namespace AppBundle\Form\Member;


use AppBundle\Entity\Member;
use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\ThingTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "organisation"];
        $inheritArray = ['inherit_data' => true];
        //add person fields
        $builder->add(
            ThingTrait::getThingBuilder(
                $builder->create('thing', FormType::class, $inheritArray + $transArray),
                ["translation_domain" => "thing_trait"]
            )
        );

        //add address fields
        $builder->add(
            AddressTrait::getAddressBuilder(
                $builder->create('address', FormType::class, $inheritArray + $transArray),
                ["translation_domain" => "address_trait"]
            )
        );

        //add communication fields
        $builder->add(
            CommunicationTrait::getCommunicationBuilder(
                $builder->create('communication', FormType::class, $inheritArray +$transArray),
                ["translation_domain" => "communication_trait"]
            )
        );


        $builder->add("create", SubmitType::class, $transArray);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Member::class,
        ));
    }
}