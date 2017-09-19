<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 14:32
 */

namespace AppBundle\Form\FrontendUser;


use AppBundle\Entity\FrontendUser;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseAbstractType;
use AppBundle\Form\Generic\SetPasswordType;
use AppBundle\Helper\NamingHelper;
use AppBundle\Helper\StaticMessageHelper;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendUserChangeEmailType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builderArray = ["translation_domain" => "entity_frontend_user"];
        $builder->add(
            "newEmail",
            EmailType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("newEmail")
        );

        $this->addSubmit($builder, SubmitButtonType::APPLY);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
        ));
    }
}