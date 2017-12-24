<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Generic;

use App\Form\BaseAbstractType;
use App\Helper\NamingHelper;
use App\Helper\StaticMessageHelper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class RemoveThingType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderArray = ['translation_domain' => 'remove'];
        $builder->add(
            'confirmConsequences',
            CheckboxType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('confirmConsequences') + ['mapped' => false]
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
