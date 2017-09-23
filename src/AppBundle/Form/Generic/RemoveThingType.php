<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14/05/2017
 * Time: 10:32
 */

namespace AppBundle\Form\Generic;


use AppBundle\Form\BaseAbstractType;
use AppBundle\Helper\NamingHelper;
use AppBundle\Helper\StaticMessageHelper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class RemoveThingType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderArray = ["translation_domain" => "remove"];
        $builder->add(
            "confirmConsequences",
            CheckboxType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("confirmConsequences") + ["mapped" => false]
        );
        $this->addSubmit($builder, $options[StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION]);
    }
}