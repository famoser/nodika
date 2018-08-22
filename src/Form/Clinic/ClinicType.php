<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Clinic;

use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Form\Base\BaseAbstractType;
use App\Form\Traits\Address\AddressType;
use App\Form\Traits\Communication\CommunicationType;
use App\Form\Traits\Thing\ThingType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClinicType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            "doctors",
            EntityType::class,
            ["class" => Doctor::class, "multiple" => true, "by_reference" => false, "translation_domain" => "entity_doctor", "label" => "entity.plural"]
        );

        $builder->add("thing", ThingType::class, ["inherit_data" => true]);
        $builder->add("communication", CommunicationType::class, ["inherit_data" => true]);
        $builder->add("address", AddressType::class, ["inherit_data" => true]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Clinic::class,
            'translation_domain' => 'entity_clinic'
        ]);
    }
}
