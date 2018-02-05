<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Newsletter;

use App\Entity\Newsletter;
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\PersonTrait;
use App\Enum\SubmitButtonType;
use App\Form\BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterForPreviewType extends BaseAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder = Newsletter::getBuilderStatic($builder);

        $this->addTrait($builder, PersonTrait::class);
        $this->addTrait($builder, CommunicationTrait::class);

        $this->addSubmit($builder, SubmitButtonType::SEND);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Newsletter::class,
        ]);
    }
}
