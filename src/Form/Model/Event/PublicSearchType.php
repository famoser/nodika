<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Model\Event;

/*
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:13
 */

use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Form\Base\BaseAbstractType;
use App\Form\Traits\StartEnd\StartEndType;
use App\Model\Event\SearchModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicSearchType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startEnd', StartEndType::class, ['inherit_data' => true]);
        $builder->add('clinic', EntityType::class, ['class' => Clinic::class, 'required' => false, 'label' => 'entity.name', 'translation_domain' => 'entity_clinic']);
        $builder->add('doctor', EntityType::class, ['class' => Doctor::class, 'required' => false, 'label' => 'entity.name', 'translation_domain' => 'entity_doctor']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchModel::class,
            'translation_domain' => 'model_event_search',
        ]);
    }
}
