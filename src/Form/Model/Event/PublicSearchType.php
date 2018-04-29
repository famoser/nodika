<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Model\Event;

/*
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:13
 */

use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Form\Base\BaseAbstractType;
use App\Form\Traits\StartEnd\StartEndType;
use App\Model\Event\SearchModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicSearchType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startEnd', StartEndType::class, ["inherit_data" => true]);
        $builder->add('member', EntityType::class, ["class" => Member::class, "required" => false, "label" => "entity.name", "translation_domain" => "entity_member"]);
        $builder->add('frontendUser', EntityType::class, ["class" => FrontendUser::class, "required" => false, "label" => "entity.name", "translation_domain" => "entity_frontend_user"]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchModel::class,
            'translation_domain' => 'model_event_search'
        ]);
    }
}
