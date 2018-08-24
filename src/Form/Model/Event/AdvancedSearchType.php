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

use App\Form\Base\BaseAbstractType;
use App\Model\Event\SearchModel;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvancedSearchType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('search', PublicSearchType::class, ['inherit_data' => true, 'label' => false]);
        $builder->add('isConfirmed', CheckboxType::class, ['required' => false]);
        $builder->add('maxResults', NumberType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchModel::class,
            'translation_domain' => 'model_event_advanced_search',
        ]);
    }
}
