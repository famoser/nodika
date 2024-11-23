<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Model\ContactRequest;

use App\Form\Base\BaseAbstractType;
use App\Model\ContactRequest;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactRequestType extends BaseAbstractType
{
    public const CHECK_DATA = null;
    public const CHECK2_DATA = 'some string';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class);
        $builder->add('email', EmailType::class);
        $builder->add('message', TextareaType::class);
        $builder->add('check', HiddenType::class, ['required' => false, 'mapped' => false]);
        $builder->add('check2', HiddenType::class, ['mapped' => false, 'data' => self::CHECK2_DATA]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'model_contact_request',
            'data_class' => ContactRequest::class,
        ]);
    }
}
