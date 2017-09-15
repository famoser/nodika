<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 18:28
 */

namespace AppBundle\Controller;

use AppBundle\Controller\Base\BaseFrontendController;
use AppBundle\Entity\Event;
use AppBundle\Entity\Person;
use AppBundle\Enum\EventChangeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/event")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends BaseFrontendController
{
    /**
     * @Route("/{event}", name="event_view")
     * @param Request $request
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, Event $event)
    {
        return $this->render("dashboard/index.html.twig");
    }

    /**
     * @Route("/{event}/assign/{person}", name="event_assign")
     * @param Request $request
     * @param Event $event
     * @param Person $person
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAction(Request $request, Event $event, Person $person)
    {
        $member = $this->getMember();
        if ($member == null) {
            return $this->redirectToRoute("dashboard_index");
        }

        if ($member->getId() == $event->getMember()) {

            foreach ($event->getMember()->getPersons() as $person) {
                if ($person->getId() == $person->getId()) {
                    $oldEvent = clone($event);
                    $event->setPerson($person);
                    $service = $this->get("app.event_past_evaluation_service");
                    $past = $service->createEventPast($this->getPerson(), $oldEvent, $$event, EventChangeType::PERSON_ASSIGNED_BY_MEMBER);
                    $this->fastSave($past, $event);
                }
            }
        }

        return $this->redirectToRoute("event_view", ["event" => $event]);
    }
}