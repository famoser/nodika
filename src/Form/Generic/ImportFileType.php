<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13/05/2017
 * Time: 13:40
 */

namespace App\Form\Generic;

use App\Form\BaseAbstractType;
use App\Model\Form\ImportFileModel;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class ImportFileType extends BaseAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $array = ["translation_domain" => "import"] + $options;
        //add person fields
        $builder->add("file", FileType::class, $array);
        $builder->add("isCorrectFormat", CheckboxType::class, $array);
        $builder->add("import", SubmitType::class, $array);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ImportFileModel::class,
        ));
    }
}
