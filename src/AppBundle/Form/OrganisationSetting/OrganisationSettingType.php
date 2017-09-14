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
use AppBundle\Entity\OrganisationSetting;
use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\UserTrait;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseCrudAbstractType;
use AppBundle\Helper\NamingHelper;
use AppBundle\Helper\StaticMessageHelper;
use AppBundle\Repository\EventLineRepository;
use AppBundle\Repository\MemberRepository;
use function GuzzleHttp\Psr7\parse_header;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationSettingType extends BaseCrudAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderArray = ["translation_domain" => "organisation_setting"];
        $builder->add(
            "canConfirmEventBeforeDays",
            IntegerType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("canConfirmEventBeforeDays")
        );

        $builder->add(
            "mustConfirmEventBeforeDays",
            IntegerType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("mustConfirmEventBeforeDays")
        );

        $builder->add(
            "sendConfirmEventEmailDays",
            IntegerType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("sendConfirmEventEmailDays")
        );

        $this->addSubmit($builder, $options[StaticMessageHelper::FORM_SUBMIT_BUTTON_TYPE_OPTION]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => OrganisationSetting::class
        ));
        parent::configureOptions($resolver);
    }
}