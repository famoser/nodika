<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration\Api;

use App\Controller\Administration\Base\BaseController;
use App\Entity\Event;
use App\Entity\EventGeneration;
use App\Entity\EventPast;
use App\Enum\EventChangeType;
use App\Form\Event\RemoveType;
use App\Model\Breadcrumb;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/generate")
 */
class GenerateController extends BaseController
{
    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return array_merge(parent::getIndexBreadcrumbs(), [
            new Breadcrumb(
                $this->generateUrl('administration_events'),
                $this->getTranslator()->trans('events.title', [], 'administration')
            ),
            new Breadcrumb(
                $this->generateUrl('administration_event_generate'),
                $this->getTranslator()->trans('index.title', [], 'administration_event_generate')
            )
        ]);
    }
}
