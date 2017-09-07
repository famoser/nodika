<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/06/2017
 * Time: 13:01
 */

namespace AppBundle\Form\Access\Admin;


use AppBundle\Entity\AdminUser;
use AppBundle\Form\Access\Base\LoginType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminLoginType extends LoginType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AdminUser::class,
        ));
    }
}