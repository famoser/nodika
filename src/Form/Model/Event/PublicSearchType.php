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
use App\Entity\EventTag;
use App\Form\Base\BaseAbstractType;
use App\Form\Traits\StartEnd\StartEndType;
use App\Model\Event\SearchModel;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicSearchType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('startEnd', StartEndType::class, ['inherit_data' => true]);
        $builder->add('clinic', EntityType::class, ['class' => Clinic::class, 'required' => false, 'label' => 'entity.name', 'translation_domain' => 'entity_clinic', 'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('c')
                ->where('c.deletedAt IS NULL');
        }]);
        $builder->add('doctor', EntityType::class, ['class' => Doctor::class, 'required' => false, 'label' => 'entity.name', 'translation_domain' => 'entity_doctor', 'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('c')
                ->where('c.deletedAt IS NULL');
        }]);
        $builder->add('eventTags', EntityType::class, ['class' => EventTag::class, 'required' => false, 'label' => 'entity.name', 'translation_domain' => 'entity_event_tag', 'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('c')
                ->where('c.deletedAt IS NULL');
        }]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchModel::class,
            'translation_domain' => 'model_event_search',
        ]);
    }
}
