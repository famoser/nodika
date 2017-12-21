<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 21:26
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
        $resolver->setDefaults(array(
            'data_class' => Newsletter::class,
        ));
    }
}