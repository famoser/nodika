<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 19:11
 */

namespace App\Form\Member;

use App\Entity\Person;
use App\Entity\Traits\PersonTrait;
use App\Entity\Traits\UserTrait;
use App\Form\BaseAbstractType;
use App\Form\FrontendUser\FrontendUserRegisterType;
use App\Helper\NamingHelper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberInviteType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addTrait($builder, PersonTrait::class);

        $builder->add($builder->create(
            "frontendUser",
            FrontendUserRegisterType::class,
            ["agb" => false] + NamingHelper::traitNameToTranslationForBuilder(UserTrait::class) +
            ["label_attr" => ["class" => "sub-form-label"], "attr" => ["class" => "sub-form-control"]]
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
        parent::configureOptions($resolver);
    }
}
