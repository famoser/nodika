<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 14:32
 */

namespace App\Form\FrontendUser;

use App\Entity\FrontendUser;
use App\Enum\SubmitButtonType;
use App\Form\BaseAbstractType;
use App\Helper\NamingHelper;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendUserChangeEmailType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builderArray = ["translation_domain" => "entity_frontend_user"];
        $builder->add(
            "email",
            EmailType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("email")
        );

        $this->addSubmit($builder, SubmitButtonType::APPLY);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => FrontendUser::class,
        ));
    }
}
