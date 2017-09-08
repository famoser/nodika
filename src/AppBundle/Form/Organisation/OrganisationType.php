<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 13:57
 */

namespace AppBundle\Form\Organisation;


use AppBundle\Entity\Organisation;
use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\ThingTrait;
use AppBundle\Form\BaseCrudAbstractType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationType extends BaseCrudAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addTrait($builder, ThingTrait::class);
        $this->addTrait($builder, AddressTrait::class);
        $this->addTrait($builder, CommunicationTrait::class);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Organisation::class,
        ));
    }
}