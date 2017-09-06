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
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseAbstractType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class RegisterForPreviewType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "entity_newsletter"];
        $builder->add(
            "choice", ChoiceType::class,
            [
                "translation_domain" => "entity_newsletter",
                "label" => "choice"
            ] +
            NewsletterChoice::getChoicesForBuilder()
        );
        $builder->add(
            "email", EmailType::class,
            [
                "translation_domain" => "entity_newsletter",
                "label" => "email"
            ]
        );
        $builder->add(
            "givenName", TextType::class,
            [
                "translation_domain" => "entity_newsletter",
                "label" => "given_name"
            ]
        );
        $builder->add(
            "familyName", TextType::class,
            [
                "translation_domain" => "entity_newsletter",
                "label" => "family_name"
            ]
        );
        $builder->add(
            "message", TextType::class,
            [
                "translation_domain" => "entity_newsletter",
                "label" => "message"
            ]
        );


        $this->addSubmit($builder, SubmitButtonType::SEND);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Newsletter::class,
        ));
    }
}