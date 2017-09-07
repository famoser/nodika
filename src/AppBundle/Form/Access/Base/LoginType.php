<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:21
 */

namespace AppBundle\Form\Access\Base;


use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Traits\UserTrait;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseAbstractType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder = UserTrait::getUserBuilder($builder);
        $this->addSubmit($builder, SubmitButtonType::LOGIN);
    }
}