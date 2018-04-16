<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\EventGeneration;

use App\Entity\EventGeneration;
use App\Form\Base\BaseAbstractType;
use App\Form\Traits\StartEnd\StartEndType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasicDataType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("startEnd", StartEndType::class, ["inherit_data" => true]);
        $builder->add("cronExpression", TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventGeneration::class,
            'translation_domain' => 'entity_event_generation'
        ]);
    }
}
