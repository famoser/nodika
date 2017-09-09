<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/05/2017
 * Time: 10:21
 */

namespace AppBundle\Form\Person;


use AppBundle\Entity\Person;
use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\PersonTrait;
use AppBundle\Form\BaseCrudAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends BaseCrudAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addTrait($builder, PersonTrait::class);
        $this->addTrait($builder, AddressTrait::class);
        $this->addTrait($builder, CommunicationTrait::class);
        parent::buildForm($builder, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class
        ]);
        parent::configureOptions($resolver);
    }
}