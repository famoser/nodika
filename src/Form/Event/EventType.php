<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Event;

/*
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:13
 */

use App\Entity\Event;
use App\Entity\EventTag;
use App\Form\Base\BaseAbstractType;
use App\Form\Traits\StartEnd\StartEndType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startEnd', StartEndType::class, ["inherit_data" => true]);
        $builder->add('event', \App\Form\Traits\Event\EventType::class, ["inherit_data" => true]);
        $builder->add('eventTags', EntityType::class, ["class" => EventTag::class, "multiple" => true, "translation_domain" => "entity_event_tag", "label" => "entity.plural", "required" => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'translation_domain' => 'entity_event'
        ]);
    }
}
