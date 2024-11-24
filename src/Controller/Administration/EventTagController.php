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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[\Symfony\Component\Routing\Attribute\Route(path: '/event_tag')]
class EventTagController extends BaseController
{
    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/new', name: 'administration_event_tag_new')]
    public function new(Request $request, ManagerRegistry $registry)
    {
        $myForm = $this->handleCreateForm($request, new EventTag());

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/event_tag/new.html.twig', $arr);
    }

    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/{eventTag}/edit', name: 'administration_event_tag_edit')]
    public function edit(Request $request, EventTag $eventTag, ManagerRegistry $registry)
    {
        $myForm = $this->handleUpdateForm($request, $eventTag);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/event_tag/edit.html.twig', $arr);
    }

    /**
     * @return Response
     */
    #[\Symfony\Component\Routing\Attribute\Route(path: '/{eventTag}/remove', name: 'administration_event_tag_remove')]
    public function remove(Request $request, EventTag $eventTag, ManagerRegistry $registry)
    {
        $myForm = $this->handleRemoveForm($request, $eventTag);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/event_tag/remove.html.twig', $arr);
    }
}
