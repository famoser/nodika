<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 19:11
 */

namespace AppBundle\Form\Member;


use AppBundle\Entity\Member;
use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\ThingTrait;
use AppBundle\Form\BaseCrudAbstractType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends BaseCrudAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addTrait($builder, ThingTrait::class, ["translation_domain" => "entity_member", "label" => "entity.name"]);
        $this->addTrait($builder, AddressTrait::class);
        $this->addTrait($builder, CommunicationTrait::class);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Member::class,
        ));
    }
}