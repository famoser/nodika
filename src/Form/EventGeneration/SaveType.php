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
use App\Entity\EventTag;
use App\Form\Base\BaseAbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SaveType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("eventDescription", TextType::class, ["required" => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventGeneration::class,
            'translation_domain' => 'entity_event_generation'
        ]);
    }
}
