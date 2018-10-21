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
use App\Model\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
            ),
        ]);
    }
}
