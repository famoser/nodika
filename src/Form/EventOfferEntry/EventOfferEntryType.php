<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\EventOfferEntry;

use App\Entity\Event;
use App\Entity\EventOffer;
use App\Entity\EventOfferAuthorization;
use App\Entity\EventOfferEntry;
use App\Form\Base\BaseAbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventOfferEntryType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('event', EntityType::class, ['class' => Event::class]);
        $builder->add('eventOffer', EntityType::class, ['class' => EventOffer::class]);
        $builder->add('eventOfferAuthorization', EntityType::class, ['class' => EventOfferAuthorization::class]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventOfferEntry::class,
            'translation_domain' => 'entity_event_offer_entry',
        ]);
    }
}
