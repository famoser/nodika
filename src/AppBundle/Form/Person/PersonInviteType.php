<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 19:11
 */

namespace AppBundle\Form\Person;


use AppBundle\Entity\Person;
use AppBundle\Entity\Traits\UserTrait;
use AppBundle\Form\BaseAbstractType;
use AppBundle\Form\FrontendUser\FrontendUserRegisterType;
use AppBundle\Helper\NamingHelper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonInviteType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add($builder->create(
            "frontendUser",
            FrontendUserRegisterType::class, ["agb" => false] + NamingHelper::traitNameToTranslationForBuilder(UserTrait::class) +
            ["label_attr" => ["class" => "sub-form-label"], "attr" => ["class" => "sub-form-control"]]));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
        parent::configureOptions($resolver);
    }
}