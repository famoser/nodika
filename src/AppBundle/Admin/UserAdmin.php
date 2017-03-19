<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/12/2016
 * Time: 12:52
 */

namespace AppBundle\Admin;


use AppBundle\Enum\ChoicesArrayConsumer;
use AppBundle\Enum\LeisureActivityLevel;
use AppBundle\Enum\NutritionCategory;
use AppBundle\Enum\WorkActivityLevel;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class UserAdmin extends AbstractAdmin
{
    /**
     * fields to show on filter
     *
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('email');
    }

    /**
     * fields to show on list
     *
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('email')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }


    /**
     * fields to show on edit / create forms
     *
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with("Übersicht", ["class" => "col-md-4"])
            ->add('email', EmailType::class, ["disabled" => true])
            ->add('username', TextType::class, ["disabled" => true])
            ->end()
            ;

        //,
    }

    /**
     * fields to show on show action
     *
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with("Übersicht", ["class" => "col-md-4"])
            ->add('email', EmailType::class, ["disabled" => true])
            ->add('username', TextType::class, ["disabled" => true])
            ->end()
            ;
    }


    public function getExportFields()
    {
        return
            [
                'id',
                'email',
                'username'
            ];
    }
}