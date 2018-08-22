<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\EventGenerationDateException;

use App\Entity\Clinic;
use App\Entity\EventGeneration;
use App\Entity\EventGenerationDateException;
use App\Enum\EventType;
use App\Form\Base\BaseAbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventGenerationDateExceptionType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("startDateTime", EntityType::class, ["class" => EventGeneration::class]);
        $builder->add("endDateTime", EntityType::class, ["class" => Clinic::class]);
        $builder->add("eventType", ChoiceType::class, EventType::getBuilderArguments());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventGenerationDateException::class,
            'translation_domain' => 'entity_event_generation_date_exception'
        ]);
    }
}
