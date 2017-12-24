<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Member;

use App\Entity\Member;
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\ThingTrait;
use App\Form\BaseCrudAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends BaseCrudAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addTrait($builder, ThingTrait::class, ['translation_domain' => 'entity_member', 'label' => 'entity.name']);
        $this->addTrait($builder, AddressTrait::class, ['required' => false]);
        $this->addTrait($builder, CommunicationTrait::class);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
        parent::configureOptions($resolver);
    }
}
