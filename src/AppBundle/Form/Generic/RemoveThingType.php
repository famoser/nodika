<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14/05/2017
 * Time: 10:32
 */

namespace AppBundle\Form\Generic;


use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseAbstractType;
use AppBundle\Helper\NamingHelper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class RemoveThingType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderArray = ["translation_domain" => "remove"];
        $builder->add(
            "confirmConsequences",
            CheckboxType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("confirmConsequences")
        );
        $this->addSubmit($builder, SubmitButtonType::REMOVE);
    }
}