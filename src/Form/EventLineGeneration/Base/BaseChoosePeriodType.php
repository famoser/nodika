<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\EventLineGeneration\Base;

use App\Enum\SubmitButtonType;
use App\Form\BaseAbstractType;
use App\Model\EventLineGeneration\Base\BaseConfiguration;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseChoosePeriodType extends BaseAbstractType
{
    public function addFields(FormBuilderInterface $builder, array $options)
    {
        $dateTimeOptions = ['date_widget' => 'single_text', 'time_widget' => 'single_text'];
        $builder->add('startDateTime', DateTimeType::class, $options + ['label' => 'choose_period.start_date_time'] + $dateTimeOptions);
        $builder->add('endDateTime', DateTimeType::class, $options + ['label' => 'choose_period.end_date_time'] + $dateTimeOptions);
        $builder->add('lengthInHours', IntegerType::class, $options + ['label' => 'choose_period.length_in_hours']);

        $builder->add(
            'submit',
            SubmitType::class,
            SubmitButtonType::getTranslationForBuilder(SubmitButtonType::NEXT)
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BaseConfiguration::class,
        ]);
    }
}
