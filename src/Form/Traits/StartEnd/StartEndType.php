<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Traits\StartEnd;

use App\Form\Base\BaseAbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StartEndType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateArray = ['date_widget' => 'single_text', 'time_widget' => 'single_text'];

        $builder->add('startDateTime', DateTimeType::class, $dateArray);
        $builder->add('endDateTime', DateTimeType::class, $dateArray);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'trait_start_end',
            'label' => 'trait.name',
        ]);
    }
}
