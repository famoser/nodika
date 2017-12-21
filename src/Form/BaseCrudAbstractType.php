<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/09/2017
 * Time: 15:16
 */

namespace App\Form;


use App\Enum\SubmitButtonType;
use App\Helper\StaticMessageHelper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseCrudAbstractType extends BaseAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addSubmit($builder, $options[StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION]);
        $resolver->setDefault(StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION, SubmitButtonType::NOT_SET);
    }
}