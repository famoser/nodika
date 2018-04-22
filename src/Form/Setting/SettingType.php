<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Setting;

/*
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:13
 */

use App\Entity\Event;
use App\Entity\EventTag;
use App\Entity\Setting;
use App\Form\Base\BaseAbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('organisationName', TextType::class);
        $builder->add('supportMail', TextType::class);
        $builder->add("canConfirmDaysAdvance", NumberType::class);
        $builder->add("mustConfirmDaysAdvance", NumberType::class);
        $builder->add("sendRemainderDaysInterval", NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Setting::class,
            'translation_domain' => 'entity_setting'
        ]);
    }
}
