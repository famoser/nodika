<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 4/1/18
 * Time: 2:03 PM
 */

namespace App\Form\Model\Event;


use App\Form\BaseAbstractType;
use App\Model\Event\SearchModel;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ['translation_domain' => 'event_line'];
        $builder->add('displayOrder', NumberType::class, $transArray);
        $this->addTrait($builder, ThingTrait::class, ['translation_domain' => 'entity_event_line', 'label' => 'entity.name']);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchModel::class,
        ]);
    }
}
