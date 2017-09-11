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
use AppBundle\Entity\EventLineGeneration;
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
 * @Route("/generate/round_robin")
 * @Security("has_role('ROLE_USER')")
 */
class GenerateRoundRobinController extends BaseController
{
    /**
     * @Route("/", name="administration_organisation_event_line_generate_round_robin")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @return Response
     */
    public function roundRobinAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $eventLineGeneration = new EventLineGeneration();
        $eventLineGeneration->setGenerationDate(new \DateTime());
        $eventLineGeneration->setEventLine($eventLine);

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        return $this->render(
            'administration/organisation/event_line/generate/round_robin.html.twig', $arr
        );
    }
}