<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 21/06/2017
 * Time: 10:06
 */

namespace AppBundle\Form\AdminUser;


use AppBundle\Entity\AdminUser;
use AppBundle\Entity\CraftTag;
use AppBundle\Entity\Traits\ThingTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditAdminUser extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "admin_moderators"];
        $entityTransArray = ["translation_domain" => "admin_user"];

        //add thing fields
        $builder->add("email", EmailType::class, $entityTransArray);
        $builder->add("plainPassword", PasswordType::class, $entityTransArray);

        $builder->add("save", SubmitType::class, $transArray);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(["data_class" => AdminUser::class]);
    }

}