<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\FrontendUser;

use App\Entity\FrontendUser;
use App\Enum\SubmitButtonType;
use App\Form\BaseAbstractType;
use App\Helper\NamingHelper;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendUserChangeEmailType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builderArray = ['translation_domain' => 'entity_frontend_user'];
        $builder->add(
            'email',
            EmailType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('email')
        );

        $this->addSubmit($builder, SubmitButtonType::APPLY);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FrontendUser::class,
        ]);
    }
}
