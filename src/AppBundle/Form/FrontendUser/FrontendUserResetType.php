<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:21
 */

namespace AppBundle\Form\FrontendUser;


use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Traits\UserTrait;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendUserResetType extends BaseAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder = UserTrait::getRegisterUserBuilder($builder);
        $this->addSubmit($builder, SubmitButtonType::REGISTER);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => FrontendUser::class,
        ));
    }
}