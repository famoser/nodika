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

use App\Entity\Setting;
use App\Form\Base\BaseAbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('canConfirmDaysAdvance', NumberType::class);
        $builder->add('mustConfirmDaysAdvance', NumberType::class);
        $builder->add('sendRemainderDaysInterval', NumberType::class);
        $builder->add('doctorsCanEditSelf', CheckboxType::class, ['required' => false]);
        $builder->add('doctorsCanEditClinics', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Setting::class,
            'translation_domain' => 'entity_setting',
        ]);
    }
}
