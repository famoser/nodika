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
use App\Form\EventGenerationDateException\EventGenerationDateExceptionType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WeightsType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $numOptions = ['scale' => 3];
        $collOptions = ['allow_add' => true, 'allow_delete' => true];

        $builder->add('weekdayWeight', NumberType::class, $numOptions);
        $builder->add('saturdayWeight', NumberType::class, $numOptions);
        $builder->add('sundayWeight', NumberType::class, $numOptions);
        $builder->add('holidayWeight', NumberType::class, $numOptions);
        $builder->add('dateExceptions', CollectionType::class, $collOptions + ['data_class' => EventGenerationDateExceptionType::class]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventGeneration::class,
            'translation_domain' => 'entity_event_generation',
        ]);
    }
}
