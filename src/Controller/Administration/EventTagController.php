<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration;

use App\Controller\Administration\Base\BaseController;
use App\Entity\EventTag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/event_tag")
 */
class EventTagController extends BaseController
{
    /**
     * @Route("/new", name="administration_event_tag_new")
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $myForm = $this->handleCreateForm($request, new EventTag());

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/event_tag/new.html.twig', $arr);
    }

    /**
     * @Route("/{eventTag}/edit", name="administration_event_tag_edit")
     *
     * @return Response
     */
    public function editAction(Request $request, EventTag $eventTag)
    {
        $myForm = $this->handleUpdateForm($request, $eventTag);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/event_tag/edit.html.twig', $arr);
    }

    /**
     * @Route("/{eventTag}/remove", name="administration_event_tag_remove")
     *
     * @return Response
     */
    public function removeAction(Request $request, EventTag $eventTag)
    {
        $myForm = $this->handleRemoveForm($request, $eventTag);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/event_tag/remove.html.twig', $arr);
    }
}
