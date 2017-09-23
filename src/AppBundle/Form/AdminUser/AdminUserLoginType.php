<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/06/2017
 * Time: 13:01
 */

namespace AppBundle\Form\AdminUser;


use AppBundle\Entity\AdminUser;
use AppBundle\Form\Generic\LoginType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserLoginType extends LoginType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AdminUser::class,
        ));
    }
}