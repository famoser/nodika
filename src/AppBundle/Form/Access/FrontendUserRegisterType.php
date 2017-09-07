<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:21
 */

namespace AppBundle\Form\Access\FrontendUser;


use AppBundle\Entity\BusinessUser;
use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\PersonTrait;
use AppBundle\Entity\Traits\ThingTrait;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseAbstractType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendUserRegisterType extends BaseAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder = FrontendUser::getBuilderStatic($builder);

        $this->addTrait($builder, PersonTrait::class);
        $this->addTrait($builder, AddressTrait::class);
        $this->addTrait($builder, CommunicationTrait::class);


        $this->addSubmit($builder, SubmitButtonType::REGISTER);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => FrontendUser::class,
        ));
    }
}