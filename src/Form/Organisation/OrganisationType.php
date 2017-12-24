<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Organisation;

use App\Entity\Organisation;
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\ThingTrait;
use App\Form\BaseCrudAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationType extends BaseCrudAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addTrait($builder, ThingTrait::class, ['translation_domain' => 'entity_organisation', 'label' => 'entity.name']);
        $this->addTrait($builder, AddressTrait::class);
        $this->addTrait($builder, CommunicationTrait::class);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Organisation::class,
        ]);
        parent::configureOptions($resolver);
    }
}
