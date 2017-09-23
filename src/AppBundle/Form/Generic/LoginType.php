<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:21
 */

namespace AppBundle\Form\Generic;


use AppBundle\Entity\Traits\UserTrait;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class LoginType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder = UserTrait::getLoginBuilder($builder);
        $this->addSubmit($builder, SubmitButtonType::LOGIN);
    }
}