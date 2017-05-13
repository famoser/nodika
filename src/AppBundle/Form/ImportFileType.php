<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13/05/2017
 * Time: 13:40
 */

namespace AppBundle\Form;


use AppBundle\Model\Form\ImportFileModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "import"];
        //add person fields
        $builder->add("file", FileType::class, $transArray);
        $builder->add("isCorrectFormat", CheckboxType::class, $transArray);
        $builder->add("import", SubmitType::class, $transArray);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ImportFileModel::class,
        ));
    }
}