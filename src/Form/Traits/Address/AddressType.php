<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Traits\Address;

use App\Form\Base\BaseAbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('street', TextType::class, ['required' => false]);
        $builder->add('streetNr', TextType::class, ['required' => false]);
        $builder->add('addressLine', TextType::class, ['required' => false]);
        $builder->add('postalCode', NumberType::class, ['required' => false]);
        $builder->add('city', TextType::class, ['required' => false]);
        $builder->add('country', CountryType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'trait_address',
            'label' => 'trait.name',
        ]);
    }
}
