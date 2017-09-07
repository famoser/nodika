<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 21/06/2017
 * Time: 10:06
 */

namespace AppBundle\Form\AdminUser;


use AppBundle\Entity\CraftTag;
use AppBundle\Entity\Traits\ThingTrait;
use AppBundle\Form\Generic\RemoveThingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemoveAdminUser extends RemoveThingType
{

}