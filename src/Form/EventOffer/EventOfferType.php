<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\EventOffer;

use App\Entity\EventOffer;
use App\Form\Base\BaseAbstractType;
use App\Form\EventOfferEntry\EventOfferEntryType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventOfferType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("message", TextType::class, ["inherit_data" => true]);
        $builder->add('entries', CollectionType::class, ["allow_add" => true, "allow_delete" => true, "entry_type" => EventOfferEntryType::class]);
        $builder->add('authorizations', CollectionType::class, ["allow_add" => true, "allow_delete" => true, "entry_type" => EventOfferEntryType::class]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventOffer::class,
            'translation_domain' => 'entity_event_offer'
        ]);
    }
}
