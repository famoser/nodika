<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/06/2017
 * Time: 13:01
 */

namespace AppBundle\Form\Access\FrontendUser;


use AppBundle\Entity\BusinessUser;
use AppBundle\Entity\FrontendUser;
use AppBundle\Form\Access\Base\LoginType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendUserLoginType extends LoginType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => FrontendUser::class,
        ));
    }
}