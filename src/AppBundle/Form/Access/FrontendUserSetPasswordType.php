<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 14:32
 */

namespace AppBundle\Form\Access\FrontendUser;


use AppBundle\Entity\BusinessUser;
use AppBundle\Entity\FrontendUser;
use AppBundle\Form\Access\Base\SetPasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendUserSetPasswordType extends SetPasswordType
{
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        parent::buildForm($builder, FrontendUser::getTranslationDomainForBuilderStatic());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => FrontendUser::class,
        ));
    }
}