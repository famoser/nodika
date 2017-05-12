<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12/05/2017
 * Time: 16:12
 */

namespace AppBundle\Form\Member;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ImportMembersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transArray = ["translation_domain" => "member"];
        $builder->add("import_file", FileType::class, $transArray);
        $builder->add("import_file_correct", CheckboxType::class, $transArray);
        $builder->add("import", SubmitType::class, $transArray);
    }
}