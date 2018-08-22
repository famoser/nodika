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
use App\Form\EventGenerationTargetClinic\EventGenerationTargetClinicType;
use App\Form\EventGenerationTargetDoctor\EventGenerationTargetDoctorType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseRecipientsType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $collOptions = ["allow_add" => true, "allow_delete" => true];
        $builder->add("doctors", CollectionType::class, $collOptions + ["entry_type" => EventGenerationTargetDoctorType::class]);
        $builder->add("clinics", CollectionType::class, $collOptions + ["entry_type" => EventGenerationTargetClinicType::class]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventGeneration::class,
            'translation_domain' => 'entity_event_generation'
        ]);
    }
}
