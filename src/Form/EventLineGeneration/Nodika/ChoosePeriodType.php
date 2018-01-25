<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\EventLineGeneration\Nodika;

use App\Form\EventLineGeneration\Base\BaseChoosePeriodType;
use App\Model\EventLineGeneration\Nodika\NodikaConfiguration;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoosePeriodType extends BaseChoosePeriodType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addFields($builder, ['translation_domain' => 'administration_organisation_event_line_generate_nodika']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NodikaConfiguration::class,
        ]);
    }
}
