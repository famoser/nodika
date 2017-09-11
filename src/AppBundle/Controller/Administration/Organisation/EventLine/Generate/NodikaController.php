<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace AppBundle\Controller\Administration\Organisation\EventLine\Generate;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\EventLineGeneration;
use AppBundle\Entity\Organisation;
use AppBundle\Enum\DistributionType;
use AppBundle\Form\EventLineGeneration\RoundRobin\ChoosePeriodType;
use AppBundle\Model\EventLineGeneration\GenerationResult;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaConfiguration;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaOutput;
use AppBundle\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use AppBundle\Security\Voter\EventLineGenerationVoter;
use AppBundle\Security\Voter\EventLineVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/nodika")
 * @Security("has_role('ROLE_USER')")
 */
class NodikaController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_event_line_generate_nodika_new")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @return Response
     */
    public function newAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $this->denyAccessUnlessGranted(EventLineVoter::ADMINISTRATE, $eventLine);

        $eventLineGeneration = new EventLineGeneration();
        $eventLineGeneration->setEventLine($eventLine);
        $eventLineGeneration->setCreatedByPerson($this->getPerson());
        $eventLineGeneration->setCreatedAtDateTime(new \DateTime());
        $eventLineGeneration->setDistributionConfiguration(new NodikaConfiguration(null));
        $eventLineGeneration->setDistributionOutput(new NodikaOutput());
        $eventLineGeneration->setDistributionType(DistributionType::NODIKA);
        $eventLineGeneration->setGenerationResult(new GenerationResult(null));
        $this->fastSave($eventLineGeneration);

        return $this->redirectToRoute(
            "administration_organisation_event_line_generate_round_robin_choose_period",
            ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $eventLineGeneration->getId()]
        );
    }
}