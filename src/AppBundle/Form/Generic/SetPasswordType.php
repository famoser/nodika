<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 14:32
 */

namespace AppBundle\Form\Generic;


use AppBundle\Entity\Traits\UserTrait;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class SetPasswordType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder = UserTrait::getSetPasswordBuilder($builder);
        $this->addSubmit($builder, SubmitButtonType::SET_PASSWORD);
    }
}