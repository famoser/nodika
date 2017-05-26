<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14/05/2017
 * Time: 10:32
 */

namespace AppBundle\Form\Generic;


use AppBundle\Entity\Traits\ThingTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemoveThingType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "remove"];
        //add person fields
        $builder->add("confirmConsequences", CheckboxType::class, $transArray);
        $builder->add("remove", SubmitType::class, $transArray);
    }
}