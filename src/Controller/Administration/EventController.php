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
use App\Entity\Event;
use App\Entity\EventPast;
use App\Enum\EventChangeType;
use App\Form\Event\RemoveEventType;
use App\Model\Breadcrumb;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/events")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends BaseFormController
{
    /**
     * @Route("/new", name="administration_event_new")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $event = new Event();
        $myForm = $this->handleCreateForm(
            $request,
            $event,
            function ($manager) use ($event) {
                /* @var ObjectManager $manager */
                $eventPast = new EventPast($event, EventChangeType::MANUALLY_CREATED_BY_ADMIN);
                $manager->persist($eventPast);

                return true;
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['new_form'] = $myForm->createView();

        return $this->render('administration/event/new.html.twig', $arr);
    }

    /**
     * @Route("/{event}/edit", name="administration_event_edit")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request, Event $event)
    {
        $myForm = $this->handleUpdateForm(
            $request,
            $event,
            function ($manager) use ($event) {
                /* @var ObjectManager $manager */
                $eventPast = new EventPast($event, EventChangeType::MANUALLY_CHANGED_BY_ADMIN);
                $manager->persist($eventPast);

                return true;
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['edit_form'] = $myForm->createView();

        return $this->render('administration/event/edit.html.twig', $arr);
    }

    /**
     * @Route("/{event}/remove", name="administration_event_remove")
     *
     * @param Request $request
     *
     *
     * @param Event $event
     * @return Response
     */
    public function removeAction(Request $request, Event $event)
    {
        $myForm = $this->handleForm(
            $this->createForm(RemoveEventType::class, $event),
            $request,
            function () use ($event) {
                /* @var FormInterface $form */
                $event->delete();
                $eventPast = new EventPast($event, EventChangeType::MANUALLY_REMOVED_BY_ADMIN);

                $manager = $this->getDoctrine()->getManager();
                $manager->persist($eventPast);
                $manager->persist($event);

                return $this->redirectToRoute("administration_events");
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['form'] = $myForm->createView();

        return $this->render('administration/event/remove.html.twig', $arr);
    }

    /**
     * @Route("/{event}/history", name="administration_event_history")
     *
     * @param Event $event
     *
     * @return Response
     */
    public function historyAction(Event $event)
    {
        $arr["event"] = $event;

        return $this->render('administration/event/history.html.twig', $arr);
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
                $this->generateUrl("administration_events"),
                $this->getTranslator()->trans("events.title", [], "administration")
            )
        ];
    }
}
