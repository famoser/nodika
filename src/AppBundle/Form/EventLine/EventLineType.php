<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:11
 */

namespace AppBundle\Form\EventLine;


use AppBundle\Entity\EventLine;
use AppBundle\Entity\Traits\ThingTrait;
use AppBundle\Form\BaseCrudAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventLineType extends BaseCrudAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addTrait($builder, ThingTrait::class, ["translation_domain" => "entity_event_line", "label" => "entity.name"]);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EventLine::class,
        ));
        parent::configureOptions($resolver);
    }
}