<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\EventGenerationMember;

use App\Entity\EventGeneration;
use App\Entity\EventGenerationFrontendUser;
use App\Entity\EventGenerationMember;
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Form\Base\BaseAbstractType;
use App\Form\Traits\Address\AddressType;
use App\Form\Traits\Communication\CommunicationType;
use App\Form\Traits\Thing\ThingType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventGenerationMemberType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("eventGeneration", EntityType::class, ["class" => EventGeneration::class]);
        $builder->add("member", EntityType::class, ["class" => Member::class]);
        $builder->add("weight", NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventGenerationMember::class,
            'translation_domain' => 'entity_event_generation_member'
        ]);
    }
}
