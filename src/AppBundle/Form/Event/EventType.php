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
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseCrudAbstractType;
use AppBundle\Helper\StaticMessageHelper;
use AppBundle\Repository\EventLineRepository;
use AppBundle\Repository\MemberRepository;
use function GuzzleHttp\Psr7\parse_header;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends BaseCrudAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fillEventLine = function (FormEvent $event) use ($builder, $options) {
            $transArray = ["translation_domain" => "event"];
            $dateArray = ["date_widget" => "single_text", "time_widget" => "single_text"];
            /* @var Organisation $organisation */
            $organisation = $options["organisation"];

            $formOptions = $transArray + array(
                    'choice_label' => 'name'
                );
            $form = $event->getForm();
            $form->add('member', EntityType::class,
                $formOptions + [
                    'class' => Member::class,
                    'query_builder' => function (MemberRepository $er) use ($organisation) {
                        return $er->getByOrganisationQueryBuilder($organisation);
                    }]
            );
            $form->add("startDateTime", DateTimeType::class, $transArray + $dateArray);
            $form->add("endDateTime", DateTimeType::class, $transArray + $dateArray);

            $form->add(
                "submit",
                SubmitType::class,
                SubmitButtonType::getTranslationForBuilder($options[StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION])
            );
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
        parent::configureOptions($resolver);
    }
}