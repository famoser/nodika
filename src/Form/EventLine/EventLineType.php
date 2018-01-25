<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\EventLine;

use App\Entity\EventLine;
use App\Entity\Traits\ThingTrait;
use App\Form\BaseCrudAbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventLineType extends BaseCrudAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ['translation_domain' => 'event_line'];
        $builder->add('displayOrder', NumberType::class, $transArray);
        $this->addTrait($builder, ThingTrait::class, ['translation_domain' => 'entity_event_line', 'label' => 'entity.name']);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventLine::class,
        ]);
        parent::configureOptions($resolver);
    }
}
