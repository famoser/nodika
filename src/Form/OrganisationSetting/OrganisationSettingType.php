<?php

namespace App\Form\OrganisationSetting;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:13
 */

use App\Entity\OrganisationSetting;
use App\Form\BaseCrudAbstractType;
use App\Helper\NamingHelper;
use App\Helper\StaticMessageHelper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationSettingType extends BaseCrudAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderArray = ["translation_domain" => "entity_organisation_setting"];
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

        $builder->add(
            "tradeEventDays",
            IntegerType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("tradeEventDays")
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
