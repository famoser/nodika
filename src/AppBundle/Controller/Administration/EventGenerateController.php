<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace AppBundle\Controller\Administration;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Event;
use AppBundle\Entity\Organisation;
use AppBundle\Form\Event\ImportEventsType;
use AppBundle\Form\Generic\RemoveThingType;
use AppBundle\Form\Event\NewEventType;
use AppBundle\Helper\DateTimeFormatter;
use AppBundle\Helper\EventLineChangeHelper;
use AppBundle\Helper\FlashMessageHelper;
use AppBundle\Model\Event\ImportEventModel;
use AppBundle\Security\Voter\EventVoter;
use AppBundle\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/organisation/{organisation}/events/generate")
 * @Security("has_role('ROLE_USER')")
 */
class EventGenerateController extends BaseController
{
    /**
     * @Route("/choose", name="administration_organisation_event_generate_choose")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function chooseAction(Request $request, Organisation $organisation)
    {
        $arr = [];
        $arr["organisation"] = $organisation;
        return $this->render(
            'administration/organisation/event/generate/choose.html.twig', $arr
        );
    }

    /**
     * @Route("/nodika", name="administration_organisation_event_generate_nodika")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function nodikaAction(Request $request, Organisation $organisation)
    {
        $arr = [];
        $arr["organisation"] = $organisation;
        return $this->render(
            'administration/organisation/event/generate/nodika.html.twig', $arr
        );
    }

    /**
     * @Route("/round_robin", name="administration_organisation_event_generate_round_robin")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function roundRobinAction(Request $request, Organisation $organisation)
    {
        $arr = [];
        $arr["organisation"] = $organisation;
        return $this->render(
            'administration/organisation/event/generate/round_robin.html.twig', $arr
        );
    }
}