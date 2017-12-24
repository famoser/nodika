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
use App\Entity\Traits\UserTrait;
use App\Enum\SubmitButtonType;
use App\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendUserRegisterType extends BaseAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder = UserTrait::getRegisterUserBuilder($builder, [], $options['agb']);

        $this->addSubmit($builder, SubmitButtonType::REGISTER);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FrontendUser::class,
            'agb' => true,
        ]);
    }
}
