<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/09/2017
 * Time: 15:16
 */

namespace AppBundle\Form;


use AppBundle\Enum\SubmitButtonType;
use Symfony\Component\Form\AbstractType;
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
}