<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Event;

/*
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:13
 */

use App\Entity\Event;
use App\Entity\EventLine;
use App\Entity\FrontendUser;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Enum\SubmitButtonType;
use App\Form\Base\BaseAbstractType;
use App\Form\BaseCrudAbstractType;
use App\Form\Person\PersonType;
use App\Helper\StaticMessageHelper;
use App\Repository\MemberRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends BaseAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateArray = ['date_widget' => 'single_text', 'time_widget' => 'single_text'];

        $builder->add('startDateTime', DateTimeType::class, $dateArray);
        $builder->add('endDateTime', DateTimeType::class, $dateArray);
        $builder->add('member', EntityType::class, ["class" => Member::class]);
        $builder->add('person', EntityType::class, ["class" => FrontendUser::class]);
        $builder->add('eventLine', EntityType::class, ['class' => EventLine::class]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'translation_domain' => 'entity_event'
        ]);
    }
}
