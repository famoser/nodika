<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:11
 */

namespace App\Form\EventLine;

use App\Entity\EventLine;
use App\Entity\Traits\ThingTrait;
use App\Form\BaseCrudAbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventLineType extends BaseCrudAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "event_line"];
        $builder->add("displayOrder", NumberType::class, $transArray);
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
