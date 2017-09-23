<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/09/2017
 * Time: 15:16
 */

namespace AppBundle\Form;


use AppBundle\Enum\SubmitButtonType;
use AppBundle\Helper\NamingHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class BaseAbstractType extends AbstractType
{
    /**
     * adds a submit button styled as defined by $submitType
     *
     * @param FormBuilderInterface $builder
     * @param $submitType
     */
    protected function addSubmit(FormBuilderInterface $builder, $submitType)
    {
        $builder->add("submit", SubmitType::class, SubmitButtonType::getTranslationForBuilder($submitType));
    }

    /**
     * creates something similar to
     *
     * $builder->add(
     *    CommunicationTrait::getCommunicationBuilder(
     *        $builder->create(
     *            'communication',
     *            FormType::class,
     *            $inheritArray +
     *            NamingHelper::traitNameToTranslationForBuilder(CommunicationTrait::class)
     *        )
     *    )
     * );
     * @param FormBuilderInterface $builder
     * @param $className
     * @param array $builderArgs the arguments submitted to the Trait builder
     * @param array $args the arguments submitted to the Trait builder method
     */
    protected function addTrait(FormBuilderInterface $builder, $className, $builderArgs = [], $args = [])
    {
        $relevantName = substr($className, strrpos($className, "\\") + 1, -5);
        $builderCall = "get" . $relevantName . "Builder";
        $subBuilder = $builder->create(
            strtolower($relevantName),
            FormType::class,
            ['inherit_data' => true] +
            $builderArgs + NamingHelper::traitNameToTranslationForBuilder($className) +
            ["label_attr" => ["class" => "sub-form-label"], "attr" => ["class" => "sub-form-control"]]
        );
        $builder->add($className::$builderCall($subBuilder, $args));
    }
}