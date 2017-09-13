<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 14:30
 */

namespace AppBundle\Form\EventLineGeneration\Nodika;


use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\BaseAbstractType;
use AppBundle\Form\EventLineGeneration\Base\BaseChoosePeriodType;
use AppBundle\Model\EventLineGeneration\Base\BaseConfiguration;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaConfiguration;
use AppBundle\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoosePeriodType extends BaseChoosePeriodType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addFields($builder, ["translation_domain" => "nodika"]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NodikaConfiguration::class
        ));
    }
}