<?php

namespace AppBundle\Form\Event;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:13
 */

use AppBundle\Entity\Event;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\ThingTrait;
use AppBundle\Repository\EventLineRepository;
use AppBundle\Repository\MemberRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "event"];
        $dateArray = ["date_widget" => "single_text", "time_widget" => "single_text"];
        $requiredFalse = ["required" => false];

        /* @var Organisation $organisation */
        $organisation = $options["organisation"];
        $fillEventLine = function (FormEvent $event) use ($organisation, $transArray, $dateArray) {
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
            $form->add('member', EntityType::class,
                $formOptions + [
                    'class' => Member::class,
                    'query_builder' => function (MemberRepository $er) use ($organisation) {
                        return $er->getByOrganisationQueryBuilder($organisation);
                    }]
            );
            $form->add("startDateTime", DateTimeType::class, $transArray + $dateArray);
            $form->add("endDateTime", DateTimeType::class, $transArray + $dateArray);
            $form->add("create", SubmitType::class, $transArray);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            $fillEventLine
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Event::class,
            'organisation' => null
        ));
    }
}