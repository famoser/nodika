<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Traits\Event;

use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Form\Base\BaseAbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends BaseAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('clinic', EntityType::class, ["class" => Clinic::class, "translation_domain" => "entity_clinic", "label" => "entity.name", "required" => false]);
        $builder->add('doctor', EntityType::class, ["class" => Doctor::class, "translation_domain" => "entity_doctor", "label" => "entity.name", "required" => false]);
        $builder->add('eventType', ChoiceType::class, \App\Enum\EventType::getBuilderArguments());
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'trait_event',
            'label' => 'trait.name'
        ]);
    }
}
