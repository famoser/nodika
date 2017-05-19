<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:11
 */

namespace AppBundle\Form\EventLine;


use AppBundle\Entity\EventLine;
use AppBundle\Entity\Traits\ThingTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewEventLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "event_line"];
        $inheritArray = ['inherit_data' => true];

        //add person fields
        $builder->add(
            ThingTrait::getThingBuilder(
                $builder->create('thing', FormType::class, $inheritArray + $transArray),
                ["translation_domain" => "thing_trait"]
            )
        );

        $builder->add("create", SubmitType::class, $transArray);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EventLine::class,
        ));
    }
}