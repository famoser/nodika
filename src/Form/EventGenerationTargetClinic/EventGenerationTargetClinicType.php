<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\EventGenerationTargetClinic;

use App\Entity\EventGeneration;
use App\Entity\EventGenerationTargetClinic;
use App\Entity\Clinic;
use App\Form\Base\BaseAbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventGenerationTargetClinicType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("eventGeneration", EntityType::class, ["class" => EventGeneration::class]);
        $builder->add("clinic", EntityType::class, ["class" => Clinic::class]);
        $builder->add("weight", NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventGenerationTargetClinic::class,
            'translation_domain' => 'entity_event_generation_clinic'
        ]);
    }
}
