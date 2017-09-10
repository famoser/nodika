<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace AppBundle\Controller\Administration\Organisation\EventLine;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\Event\ImportEventsType;
use AppBundle\Form\Event\EventType;
use AppBundle\Form\Generic\RemoveThingType;
use AppBundle\Helper\DateTimeFormatter;
use AppBundle\Helper\EventPastEvaluationHelper;
use AppBundle\Helper\StaticMessageHelper;
use AppBundle\Model\Event\ImportEventModel;
use AppBundle\Security\Voter\EventLineVoter;
use AppBundle\Security\Voter\EventVoter;
use AppBundle\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * @Route("/generate")
 * @Security("has_role('ROLE_USER')")
 */
class GenerateController extends BaseController
{
    /**
     * @Route("/choose", name="administration_organisation_event_line_generate_choose")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function chooseAction(Request $request, Organisation $organisation)
    {
        $arr = [];
        $arr["organisation"] = $organisation;
        return $this->render(
            'administration/organisation/event_line/generate/choose.html.twig', $arr
        );
    }

    /**
     * @Route("/nodika", name="administration_organisation_event_line_generate_nodika")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function nodikaAction(Request $request, Organisation $organisation)
    {
        $arr = [];
        $arr["organisation"] = $organisation;
        return $this->render(
            'administration/organisation/event_line/generate/nodika.html.twig', $arr
        );
    }

    /**
     * @Route("/round_robin", name="administration_organisation_event_line_generate_round_robin")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function roundRobinAction(Request $request, Organisation $organisation)
    {
        $arr = [];
        $arr["organisation"] = $organisation;
        return $this->render(
            'administration/organisation/event_line/generate/round_robin.html.twig', $arr
        );
    }
}