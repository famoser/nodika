<?php

namespace AppBundle\Form\Event;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:13
 */

use AppBundle\Entity\EventLine;
use AppBundle\Entity\Organisation;
use AppBundle\Form\Generic\ImportFileType;
use AppBundle\Model\Event\ImportEventModel;
use AppBundle\Repository\EventLineRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportEventsType extends ImportFileType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "event"];
        $inheritArray = ['inherit_data' => true];

        /* @var Organisation $organisation */
        $organisation = $options["organisation"];
        $fillEventLine = function (FormEvent $event) use ($organisation, $transArray, $inheritArray, $builder, $options) {
            $formOptions = $transArray + array(
                    'choice_label' => 'name'
                );
            $form = $event->getForm();
            $form->add('eventLine', EntityType::class,
                $formOptions + [
                    'class' => EventLine::class,
                    'query_builder' => function (EventLineRepository $er) use ($organisation) {
                        return $er->getByOrganisationQueryBuilder($organisation);
                    }]
            );
            parent::buildFormInternal($form, $options + $inheritArray);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            $fillEventLine
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ImportEventModel::class,
            'organisation' => null
        ));
    }
}