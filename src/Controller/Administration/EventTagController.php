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

use App\Controller\Base\BaseFormController;
use App\Entity\EventTag;
use App\Model\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/event_tag")
 * @Security("has_role('ROLE_USER')")
 */
class EventTagController extends BaseFormController
{
    /**
     * @Route("/new", name="administration_event_tag_new")
     *
     * @param Request $request
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
     * @param Request $request
     * @param EventTag $eventTag
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
     * @param Request $request
     * @param EventTag $eventTag
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

    /**
     * get the breadcrumbs leading to this controller
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return [
            new Breadcrumb(
                $this->generateUrl("administration_index"),
                $this->getTranslator()->trans("index.title", [], "administration")
            ),
            new Breadcrumb(
                $this->generateUrl("administration_event_tags"),
                $this->getTranslator()->trans("event_tags.title", [], "administration")
            )
        ];
    }
}
