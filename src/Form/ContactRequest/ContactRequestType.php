<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/01/2018
 * Time: 19:04
 */

namespace App\Form\ContactRequest;

use App\Enum\SubmitButtonType;
use App\Form\BaseAbstractType;
use App\Helper\NamingHelper;
use App\Model\ContactRequest\ContactRequest;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactRequestType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ['translation_domain' => 'entity_contact_request'];
        $builder->add("email", EmailType::class, $transArray + NamingHelper::propertyToTranslationForBuilder("email"));
        $builder->add("name", TextType::class, $transArray + NamingHelper::propertyToTranslationForBuilder("name"));
        $builder->add("message", TextareaType::class, $transArray + NamingHelper::propertyToTranslationForBuilder("message"));
        $builder->add(
            'submit',
            SubmitType::class,
            SubmitButtonType::getTranslationForBuilder(SubmitButtonType::SEND)
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContactRequest::class
        ]);
        parent::configureOptions($resolver);
    }
}