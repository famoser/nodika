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
use App\Form\Base\BaseAbstractType;
use App\Form\BaseCrudAbstractType;
use App\Form\Traits\Thing\ThingType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventLineType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("thing", ThingType::class, ["inherit_data" => true]);
        $builder->add('displayOrder', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventLine::class,
            'translation_domain' => 'entity_event_line'
        ]);
        parent::configureOptions($resolver);
    }
}
