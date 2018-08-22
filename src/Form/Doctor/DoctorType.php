<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Doctor;

use App\Entity\Doctor;
use App\Entity\Clinic;
use App\Form\Base\BaseAbstractType;
use App\Form\Traits\Address\AddressType;
use App\Form\Traits\Communication\CommunicationType;
use App\Form\Traits\Person\PersonType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DoctorType extends BaseAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("clinics", EntityType::class, ["class" => Clinic::class, "multiple" => true, 'translation_domain' => 'entity_clinic', 'label' => 'entity.plural']);
        $builder->add("person", PersonType::class, ["inherit_data" => true]);
        $builder->add("communication", CommunicationType::class, ["inherit_data" => true]);
        $builder->add("address", AddressType::class, ["inherit_data" => true]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Doctor::class,
            'translation_domain' => 'entity_frontend_user'
        ]);
    }
}
