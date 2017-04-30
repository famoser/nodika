<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 21:26
 */

namespace AppBundle\Form\Newsletter;


use AppBundle\Entity\Newsletter;
use AppBundle\Enum\NewsletterChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class RegisterForPreviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("choice", ChoiceType::class, ["choices" => NewsletterChoice::toChoicesArray()])
            ->add("email", EmailType::class)
            ->add("givenName", TextType::class)
            ->add("familyName", TextType::class)
            ->add("message", TextareaType::class)
            ->add("submit", SubmitType::class, ["label" => "Send"]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Newsletter::class,
        ));
    }
}