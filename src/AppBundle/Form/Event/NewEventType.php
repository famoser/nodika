<?php
use AppBundle\Entity\Organisation;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:13
 */

namespace AppBundle\Form\Event;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\Member;
use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\ThingTrait;
use AppBundle\Repository\EventLineRepository;
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
        $requiredFalse = ["required" => false];

        /* @var Organisation $organisation */
        $organisation = $options["organisation"];
        $fillEventLine = function (FormEvent $event) use ($organisation, $transArray) {
            $formOptions = $transArray + array(
                    'class' => EventLine::class,
                    'choice_label' => 'name',
                    'query_builder' => function (EventLineRepository $er) use ($organisation) {
                        return $er->getByOrganisationQueryBuilder($organisation);
                    },
                );
            $event->getForm()->add('eventLine', EntityType::class, $formOptions);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            $fillEventLine
        );


        $builder->add("startDateTime", DateTimeType::class, $transArray);
        $builder->add("endDateTime", DateTimeType::class, $transArray);

        $builder->add("create", SubmitType::class, $transArray);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Event::class,
        ));
    }
}