<?php

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace AppBundle\Controller\Administration\Organisation\EventLine\Generate;


use AppBundle\Controller\Administration\Organisation\EventLine\Generate\Base\BaseGenerationController;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\EventLineGeneration;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Enum\DistributionType;
use AppBundle\Enum\EventGenerationServicePersistResponse;
use AppBundle\Enum\RoundRobinStatusCode;
use AppBundle\Form\EventLineGeneration\RoundRobin\ChoosePeriodType;
use AppBundle\Model\EventLineGeneration\Base\EventLineConfiguration;
use AppBundle\Model\EventLineGeneration\GenerationResult;
use AppBundle\Model\EventLineGeneration\RoundRobin\MemberConfiguration;
use AppBundle\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use AppBundle\Model\EventLineGeneration\RoundRobin\RoundRobinOutput;
use AppBundle\Security\Voter\EventLineGenerationVoter;
use AppBundle\Security\Voter\EventLineVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/round_robin")
 * @Security("has_role('ROLE_USER')")
 */
class RoundRobinController extends BaseGenerationController
{
    /**
     * @Route("/new", name="administration_organisation_event_line_generate_round_robin_new")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @return Response
     */
    public function newAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        return $this->render(
            'administration/organisation/event_line/generate/round_robin/new.html.twig', $arr
        );
    }

    /**
     * @Route("/start", name="administration_organisation_event_line_generate_round_robin_start")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @return Response
     */
    public function startAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $this->denyAccessUnlessGranted(EventLineVoter::ADMINISTRATE, $eventLine);

        $eventLineGeneration = new EventLineGeneration();
        $eventLineGeneration->setEventLine($eventLine);
        $eventLineGeneration->setCreatedByPerson($this->getPerson());
        $eventLineGeneration->setCreatedAtDateTime(new \DateTime());
        $eventLineGeneration->setDistributionConfiguration(new RoundRobinConfiguration(null));
        $eventLineGeneration->setDistributionOutput(new RoundRobinOutput());
        $eventLineGeneration->setDistributionType(DistributionType::ROUND_ROBIN);
        $eventLineGeneration->setGenerationResult(new GenerationResult(null));
        $this->fastSave($eventLineGeneration);

        return $this->redirectToRoute(
            "administration_organisation_event_line_generate_round_robin_choose_period",
            ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $eventLineGeneration->getId()]
        );
    }

    /**
     * @Route("/{generation}/choose_period", name="administration_organisation_event_line_generate_round_robin_choose_period")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function choosePeriodAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $form = $this->handleForm(
            $this->createForm(ChoosePeriodType::class),
            $request,
            new RoundRobinConfiguration(null),
            function ($form, $entity) use ($organisation, $eventLine, $generation, $config) {
                /* @var RoundRobinConfiguration $entity */
                $config->lengthInHours = $entity->lengthInHours;
                $config->startDateTime = $entity->startDateTime;
                $config->endDateTime = $entity->endDateTime;
                $this->saveDistributionConfiguration($generation, $config);
                return $this->redirectToRoute(
                    "administration_organisation_event_line_generate_round_robin_no_conflicts",
                    ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
                );
            }
        );

        if ($form instanceof Response) {
            return $form;
        }

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        $arr["eventLineGeneration"] = $generation;
        $arr["choosePeriodForm"] = $form->createView();
        return $this->render(
            'administration/organisation/event_line/generate/round_robin/choose_period.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/randomize_member_order", name="administration_organisation_event_line_generate_round_robin_randomize_member_order")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function randomizeMemberOrderAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $this->randomizeMemberOrder($config);
        $this->saveDistributionConfiguration($generation, $config);
        return $this->redirectToRoute(
            "administration_organisation_event_line_generate_round_robin_set_order",
            ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
        );
    }

    /**
     * @Route("/{generation}/no_conflicts", name="administration_organisation_event_line_generate_round_robin_no_conflicts")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function noConflictsAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        if ($request->getMethod() == "POST") {
            /* @var EventLineConfiguration[] $eventLineConfigurations */
            $eventLineConfigurations = [];
            foreach ($config->eventLineConfiguration as $eventLineConfiguration) {
                $eventLineConfigurations[$eventLineConfiguration->id] = $eventLineConfiguration;
            }
            foreach ($request->request->all() as $key => $value) {
                if (strpos($key, "event_line_") === 0) {
                    $eventLineId = substr($key, 11); //cut off event_line_
                    if (isset($eventLineConfigurations[$eventLineId])) {
                        $eventLineConfigurations[$eventLineId]->isEnabled = true;
                    }
                } else if ($key == "conflict_puffer_in_hours") {
                    $config->conflictPufferInHours = $value;
                }
            }
            $config->eventLineConfiguration = $eventLineConfigurations;
            $this->saveDistributionConfiguration($generation, $config);
            return $this->redirectToRoute(
                "administration_organisation_event_line_generate_round_robin_choose_members",
                ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
            );
        }

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLineConfigurations"] = $config->eventLineConfiguration;
        $arr["conflictPufferInHours"] = $config->conflictPufferInHours;
        $arr["eventLine"] = $eventLine;
        $arr["eventLineGeneration"] = $generation;
        return $this->render(
            'administration/organisation/event_line/generate/round_robin/no_conflicts.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/choose_members", name="administration_organisation_event_line_generate_round_robin_choose_members")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function chooseMembersAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        if ($request->getMethod() == "POST") {
            /* @var MemberConfiguration[] $memberConfigurations */
            $memberConfigurations = [];
            foreach ($config->memberConfigurations as $memberConfiguration) {
                $memberConfiguration->isEnabled = false;
                $memberConfigurations[$memberConfiguration->id] = $memberConfiguration;
            }
            foreach ($request->request->all() as $key => $value) {
                if (strpos($key, "member_") === 0) {
                    $memberId = substr($key, 7); //cut off member_
                    if (isset($memberConfigurations[$memberId])) {
                        $memberConfigurations[$memberId]->isEnabled = true;
                    }
                }
            }
            $config->memberConfigurations = $memberConfigurations;
            $this->saveDistributionConfiguration($generation, $config);
            return $this->redirectToRoute(
                "administration_organisation_event_line_generate_round_robin_set_order",
                ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
            );
        }

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["memberConfigurations"] = $config->memberConfigurations;
        $arr["eventLine"] = $eventLine;
        $arr["eventLineGeneration"] = $generation;
        return $this->render(
            'administration/organisation/event_line/generate/round_robin/choose_members.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/set_order", name="administration_organisation_event_line_generate_round_robin_set_order")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function setOrderAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        if ($request->getMethod() == "POST") {
            /* @var MemberConfiguration[] $memberConfigurations */
            $memberConfigurations = [];
            foreach ($config->memberConfigurations as $memberConfiguration) {
                $memberConfigurations[$memberConfiguration->id] = $memberConfiguration;
            }
            foreach ($request->request->all() as $key => $value) {
                if (strpos($key, "member_") === 0) {
                    $memberId = substr($key, 7); //cut off member_
                    if (isset($memberConfigurations[$memberId])) {
                        $memberConfigurations[$memberId]->order = $value;
                    }
                }
            }
            $config->memberConfigurations = $memberConfigurations;
            $this->saveDistributionConfiguration($generation, $config);
            return $this->redirectToRoute(
                "administration_organisation_event_line_generate_round_robin_start_generation",
                ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
            );
        }

        $onlyEnabled = [];
        foreach ($config->memberConfigurations as $memberConfiguration) {
            if ($memberConfiguration->isEnabled) {
                $onlyEnabled[] = $memberConfiguration;
            }
        }

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["memberConfigurations"] = $onlyEnabled;
        $arr["eventLine"] = $eventLine;
        $arr["eventLineGeneration"] = $generation;
        return $this->render(
            'administration/organisation/event_line/generate/round_robin/set_order.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/start_generation", name="administration_organisation_event_line_generate_round_robin_start_generation")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function startGenerationAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        $arr["eventLineGeneration"] = $generation;
        return $this->render(
            'administration/organisation/event_line/generate/round_robin/start_generation.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/do_generate", name="administration_organisation_event_line_generate_round_robin_do_generate")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function doGenerationAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        /* @var RoundRobinOutput $roundRobinOutput */
        $roundRobinOutput = $this->get("app.event_generation_service")->generateRoundRobin(
            $config,
            function ($startDate, $endDate, $assignedEventCount, $member) {
                return true;
            }
        );
        if ($roundRobinOutput instanceof RoundRobinOutput) {
            if ($roundRobinOutput->roundRobinStatusCode == RoundRobinStatusCode::SUCCESSFUL) {
                $this->saveRoundRobinOutput($generation, $roundRobinOutput);
                return $this->redirectToRoute(
                    "administration_organisation_event_line_generate_round_robin_confirm_generation",
                    ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
                );
            }
        } else {
            $translator = $this->get("translator");
            $this->displayError(
                $translator->trans(
                    RoundRobinStatusCode::getTranslation(RoundRobinStatusCode::UNKNOWN_ERROR),
                    [],
                    RoundRobinStatusCode::getTranslationDomainStatic()
                )
            );
        }
        return $this->redirectToRoute(
            "administration_organisation_event_line_generate_round_robin_start_generation",
            ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
        );
    }

    /**
     * @Route("/{generation}/confirm_generation", name="administration_organisation_event_line_generate_round_robin_confirm_generation")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function confirmGenerationAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);
        $generationResult = $this->getGenerationResult($generation);

        $memberById = [];
        foreach ($this->getDoctrine()->getRepository("AppBundle:Member")->findBy(["organisation" => $organisation->getId()]) as $item) {
            $memberById[$item->getId()] = $item;
        }

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        $arr["eventLineGeneration"] = $generation;
        $arr["generationResult"] = $generationResult;
        $arr["memberById"] = $memberById;

        return $this->render(
            'administration/organisation/event_line/generate/round_robin/confirm_generation.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/apply_generation", name="administration_organisation_event_line_generate_round_robin_apply_generation")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function applyGenerationAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $generationResult = $this->getGenerationResult($generation);

        $generationService = $this->get("app.event_generation_service");
        $resp = $generationService->persist($generation, $generationResult, $this->getPerson());
        if ($resp == EventGenerationServicePersistResponse::SUCCESSFUL) {
            return $this->redirectToRoute(
                "administration_organisation_event_line_administer",
                ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId()]
            );
        } else if ($resp == EventGenerationServicePersistResponse::MEMBER_NOT_FOUND_ANYMORE) {
            return $this->redirectToRoute(
                "administration_organisation_event_line_generate_round_robin_choose_members",
                ["organisation" => $generation->getEventLine()->getOrganisation()->getId(), "eventLine" => $generation->getEventLine()->getId(), "generation" => $generation->getId()]
            );
        } else {
            return $this->redirectToRoute(
                "administration_organisation_event_line_generate_nodika_confirm_generation",
                ["organisation" => $generation->getEventLine()->getOrganisation()->getId(), "eventLine" => $generation->getEventLine()->getId(), "generation" => $generation->getId()]
            );
        }
    }

    /**
     * @param EventLineGeneration $generation
     * @param Organisation $organisation
     * @return RoundRobinConfiguration
     */
    private function getDistributionConfiguration(EventLineGeneration $generation, Organisation $organisation)
    {
        $configuration = new RoundRobinConfiguration(json_decode($generation->getDistributionConfigurationJson()));
        $this->addMemberConfiguration($configuration, $organisation);
        $this->addEventLineConfiguration($configuration, $organisation, $generation);
        if (!$configuration->randomOrderMade) {
            $this->randomizeMemberOrder($configuration);
            $configuration->randomOrderMade = true;
        }
        $this->saveDistributionConfiguration($generation, $configuration);
        return $configuration;
    }

    /**
     * @param EventLineGeneration $generation
     * @param $configuration
     */
    private function saveDistributionConfiguration(EventLineGeneration $generation, RoundRobinConfiguration $configuration)
    {
        $generation->setDistributionConfiguration($configuration);
        $this->fastSave($generation);
    }


    /**
     * @param EventLineGeneration $generation
     * @param RoundRobinOutput $roundRobinOutput
     */
    private function saveRoundRobinOutput(EventLineGeneration $generation, RoundRobinOutput $roundRobinOutput)
    {
        $generation->setGenerationResult($roundRobinOutput->generationResult);
        $generation->setDistributionOutput($roundRobinOutput);
        $this->fastSave($generation);
    }

    /**
     * @param RoundRobinConfiguration $configuration
     */
    private function randomizeMemberOrder(RoundRobinConfiguration $configuration)
    {
        //get ids
        $ids = [];
        /* @var MemberConfiguration[] $memberConfigurations */
        $memberConfigurations = [];
        $appendMemberConfiguration = [];
        foreach ($configuration->memberConfigurations as $key => $value) {
            if ($value->isEnabled) {
                $ids[] = $value->id;
                $memberConfigurations[$value->id] = $value;
            } else {
                $value->order = 99;
                $appendMemberConfiguration[] = $value;
            }
        }

        //randomize order
        shuffle($ids);

        //recreate array
        $order = 1;
        $configuration->memberConfigurations = [];
        foreach ($ids as $id) {
            $memberConfigurations[$id]->order = $order++;
            $configuration->memberConfigurations[] = $memberConfigurations[$id];
        }

        foreach ($appendMemberConfiguration as $item) {
            $configuration->memberConfigurations[] = $item;
        }
    }

    /**
     * @param RoundRobinConfiguration $configuration
     * @param Organisation $organisation
     */
    private function addMemberConfiguration(RoundRobinConfiguration $configuration, Organisation $organisation)
    {
        /* @var Member[] $memberById */
        $memberById = [];
        foreach ($organisation->getMembers() as $member) {
            $memberById[$member->getId()] = $member;
        }
        $maxOrder = 1;

        $removeKeys = [];
        foreach ($configuration->memberConfigurations as $key => $value) {
            if (isset($memberById[$value->id])) {
                $value->name = $memberById[$value->id]->getName();
                unset($memberById[$value->id]);
            } else {
                $removeKeys[] = $key;
            }

            if ($value->order >= $maxOrder) {
                $maxOrder = $value->order;
            }

        }

        foreach ($removeKeys as $removeKey) {
            unset($configuration->memberConfigurations[$removeKey]);
        }

        foreach ($memberById as $item) {
            $newConfig = MemberConfiguration::createFromMember($item, ++$maxOrder);
            $configuration->memberConfigurations[] = $newConfig;
        }
    }

}