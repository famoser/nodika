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
use AppBundle\Enum\NodikaStatusCode;
use AppBundle\Form\EventLineGeneration\Nodika\ChoosePeriodType;
use AppBundle\Helper\DateTimeFormatter;
use AppBundle\Model\EventLineGeneration\Base\EventLineConfiguration;
use AppBundle\Model\EventLineGeneration\GenerationResult;
use AppBundle\Model\EventLineGeneration\Nodika\MemberConfiguration;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaConfiguration;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaOutput;
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
class NodikaController extends BaseGenerationController
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
        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        return $this->render(
            'administration/organisation/event_line/generate/nodika/new.html.twig', $arr
        );
    }

    /**
     * @Route("/start", name="administration_organisation_event_line_generate_nodika_start")
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
        $eventLineGeneration->setDistributionConfiguration(new NodikaConfiguration(null));
        $eventLineGeneration->setDistributionOutput(new NodikaOutput());
        $eventLineGeneration->setDistributionType(DistributionType::NODIKA);
        $eventLineGeneration->setGenerationResult(new GenerationResult(null));
        $this->fastSave($eventLineGeneration);

        return $this->redirectToRoute(
            "administration_organisation_event_line_generate_nodika_choose_period",
            ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $eventLineGeneration->getId()]
        );
    }

    /**
     * @Route("/{generation}/choose_period", name="administration_organisation_event_line_generate_nodika_choose_period")
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
            new NodikaConfiguration(null),
            function ($form, $entity) use ($organisation, $eventLine, $generation, $config) {
                /* @var NodikaConfiguration $entity */
                $config->lengthInHours = $entity->lengthInHours;
                $config->startDateTime = $entity->startDateTime;
                $config->endDateTime = $entity->endDateTime;
                $config->memberEventTypeDistributionFilled = false;
                $this->saveDistributionConfiguration($generation, $config);
                return $this->redirectToRoute(
                    "administration_organisation_event_line_generate_nodika_no_conflicts",
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
            'administration/organisation/event_line/generate/nodika/choose_period.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/no_conflicts", name="administration_organisation_event_line_generate_nodika_no_conflicts")
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
            $config->memberEventTypeDistributionFilled = false;
            $this->saveDistributionConfiguration($generation, $config);
            return $this->redirectToRoute(
                "administration_organisation_event_line_generate_nodika_choose_members",
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
            'administration/organisation/event_line/generate/nodika/no_conflicts.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/choose_members", name="administration_organisation_event_line_generate_nodika_choose_members")
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
            $config->memberEventTypeDistributionFilled = false;
            $this->saveDistributionConfiguration($generation, $config);
            return $this->redirectToRoute(
                "administration_organisation_event_line_generate_nodika_relative_distribution",
                ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
            );
        }

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["memberConfigurations"] = $config->memberConfigurations;
        $arr["eventLine"] = $eventLine;
        $arr["eventLineGeneration"] = $generation;
        return $this->render(
            'administration/organisation/event_line/generate/nodika/choose_members.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/relative_distribution", name="administration_organisation_event_line_generate_nodika_relative_distribution")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function relativeDistributionAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
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
                if (strpos($key, "member_p_") === 0) {
                    $memberId = substr($key, 9); //cut off member_p_
                    if (isset($memberConfigurations[$memberId])) {
                        $memberConfigurations[$memberId]->points = $value;
                    }
                } else if (strpos($key, "member_l_") === 0) {
                    $memberId = substr($key, 9); //cut off member_l_
                    if (isset($memberConfigurations[$memberId])) {
                        $memberConfigurations[$memberId]->luckyScore = $value;
                    }
                }
            }
            $config->memberConfigurations = $memberConfigurations;
            $config->memberEventTypeDistributionFilled = false;
            $this->saveDistributionConfiguration($generation, $config);
            return $this->redirectToRoute(
                "administration_organisation_event_line_generate_nodika_distribution_settings",
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
            'administration/organisation/event_line/generate/nodika/relative_distribution.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/distribution_settings", name="administration_organisation_event_line_generate_nodika_distribution_settings")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function distributionSettingsAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);


        $holidayString = "";
        foreach ($config->holidays as $holiday) {
            $holidayString .= $holiday->format(DateTimeFormatter::DATE_FORMAT) . ", ";
        }
        if (strlen($holidayString) > 0) {
            $holidayString = substr($holidayString, 0, -2);
        }

        if ($request->getMethod() == "POST") {
            /* @var MemberConfiguration[] $memberConfigurations */
            $memberConfigurations = [];
            foreach ($config->memberConfigurations as $memberConfiguration) {
                $memberConfigurations[$memberConfiguration->id] = $memberConfiguration;
            }
            $submissionFailure = false;
            foreach ($request->request->all() as $key => $value) {
                if ($key === "event_type_weekday") {
                    $config->eventTypeConfiguration->weekday = $value;
                } else if ($key === "event_type_saturday") {
                    $config->eventTypeConfiguration->saturday = $value;
                } else if ($key === "event_type_sunday") {
                    $config->eventTypeConfiguration->sunday = $value;
                } else if ($key === "event_type_holiday") {
                    $config->eventTypeConfiguration->holiday = $value;
                } else if ($key == "holiday_string") {
                    $holidayString = $value;
                    if (trim($value) == "") {
                        $parts = [];
                    } elseif (!strpos($value, ",")) {
                        $parts[] = $value;
                    } else {
                        $parts = explode(",", $value);
                    }
                    $sanitizedParts = [];
                    $foundInvalid = false;
                    foreach ($parts as $part) {
                        $part = trim($part);
                        //must be of the form dd.mm.yyyy
                        $parts = explode(".", $part);
                        if (!(count($parts) == 3 &&
                            is_numeric($parts[0]) && strlen($parts[0]) == 2 &&
                            is_numeric($parts[1]) && strlen($parts[1]) == 2 &&
                            is_numeric($parts[2]) && strlen($parts[2]) == 4)) {
                            $submissionFailure = true;
                            $foundInvalid = true;
                            $translator = $this->get("translator");
                            $this->displayError($translator->trans("error.date_format_invalid", ["%date%" => $part], "nodika"));
                        } else {
                            $sanitizedParts[] = $part;
                        }
                    }
                    if (!$foundInvalid) {
                        $config->holidays = [];
                        $config->holidaysFilled = true;
                        $dateTimes = [];
                        foreach ($sanitizedParts as $sanitizedPart) {
                            $date = new \DateTime($sanitizedPart);
                            $dateTimes[$date->getTimestamp()] = $date;
                        }
                        ksort($dateTimes);
                        $config->holidays = $dateTimes;
                    }
                }
            }
            $config->memberConfigurations = $memberConfigurations;
            $config->memberEventTypeDistributionFilled = false;
            $this->saveDistributionConfiguration($generation, $config);
            if (!$submissionFailure) {
                return $this->redirectToRoute(
                    "administration_organisation_event_line_generate_nodika_do_distribution",
                    ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
                );
            }
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
        $arr["eventTypeConfiguration"] = $config->eventTypeConfiguration;
        $arr["holidayString"] = $holidayString;
        return $this->render(
            'administration/organisation/event_line/generate/nodika/distribution_settings.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/do_distribution", name="administration_organisation_event_line_generate_nodika_do_distribution")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function doDistributionAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $generationService = $this->get("app.event_generation_service");
        $generationService->setEventTypeDistribution($config);
        $config->memberEventTypeDistributionFilled = true;
        $this->saveDistributionConfiguration($generation, $config);

        return $this->redirectToRoute(
            "administration_organisation_event_line_generate_nodika_distribution_confirm",
            ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
        );

    }

    /**
     * @Route("/{generation}/distribution_confirm", name="administration_organisation_event_line_generate_nodika_distribution_confirm")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function distributionConfirmAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        $arr["eventLineGeneration"] = $generation;
        $arr["memberEventTypeDistributions"] = $config->memberEventTypeDistributions;
        return $this->render(
            'administration/organisation/event_line/generate/nodika/distribution_confirm.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/assignment_settings", name="administration_organisation_event_line_generate_nodika_assignment_settings")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @return Response
     */
    public function assignmentSettingsAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $beforeEventsString = "";
        foreach ($config->beforeEvents as $beforeEvent) {
            $beforeEventsString .= $beforeEvent . ", ";
        }
        if (strlen($beforeEventsString) > 0) {
            $beforeEventsString = substr($beforeEventsString, 0, -2);
        }

        if ($request->getMethod() == "POST") {
            $translator = $this->get("translator");
            /* @var MemberConfiguration[] $memberConfigurations */
            $memberConfigurations = [];
            foreach ($config->memberConfigurations as $memberConfiguration) {
                $memberConfigurations[$memberConfiguration->id] = $memberConfiguration;
            }
            $submissionFailure = false;
            foreach ($request->request->all() as $key => $value) {
                if ($key == "before_events") {
                    $beforeEventsString = $value;
                    if (trim($value) == "") {
                        $parts = [];
                    } elseif (!strpos($value, ",")) {
                        $parts[] = $value;
                    } else {
                        $parts = explode(",", $value);
                    }
                    $sanitizedParts = [];
                    $foundInvalid = false;
                    foreach ($parts as $part) {
                        $part = trim($part);
                        if (!is_numeric($part)) {
                            $submissionFailure = true;
                            $foundInvalid = true;
                            $this->displayError($translator->trans("error.member_format_invalid", ["%part%" => $part], "nodika"));
                        } else {
                            $sanitizedParts[] = $part;
                        }
                    }
                    if (!$foundInvalid) {
                        foreach ($sanitizedParts as $sanitizedPart) {
                            if ($sanitizedPart != 0 && !isset($memberConfigurations[$sanitizedPart])) {
                                $this->displayError($translator->trans("error.member_not_found", ["%id%" => $sanitizedPart], "nodika"));
                                $submissionFailure = true;
                                $foundInvalid = true;
                                break;
                            }
                        }
                        if (!$foundInvalid) {
                            $config->beforeEvents = $sanitizedParts;
                        }
                    }
                }
            }
            $this->saveDistributionConfiguration($generation, $config);
            if (!$submissionFailure) {
                return $this->redirectToRoute(
                    "administration_organisation_event_line_generate_nodika_start_generation",
                    ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
                );
            }
        }

        $arr = [];
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        $arr["eventLineGeneration"] = $generation;
        $arr["memberConfigurations"] = $config->memberConfigurations;
        $arr["beforeEventsString"] = $beforeEventsString;
        return $this->render(
            'administration/organisation/event_line/generate/nodika/assignment_settings.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/start_generation", name="administration_organisation_event_line_generate_nodika_start_generation")
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
            'administration/organisation/event_line/generate/nodika/start_generation.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/do_generate", name="administration_organisation_event_line_generate_nodika_do_generate")
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

        /* @var NodikaOutput $nodikaOutput */
        $nodikaOutput = $this->get("app.event_generation_service")->generateNodika(
            $config,
            function ($startDate, $endDate, $assignedEventCount, $member) {
                return true;
            }
        );
        if ($nodikaOutput instanceof NodikaOutput) {
            if ($nodikaOutput->nodikaStatusCode == NodikaStatusCode::SUCCESSFUL) {
                $this->saveNodikaOutput($generation, $nodikaOutput);
                return $this->redirectToRoute(
                    "administration_organisation_event_line_generate_nodika_confirm_generation",
                    ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
                );
            }
        } else {
            $translator = $this->get("translator");
            $this->displayError(
                $translator->trans(
                    NodikaStatusCode::getTranslation(NodikaStatusCode::UNKNOWN_ERROR),
                    [],
                    NodikaStatusCode::getTranslationDomainStatic()
                )
            );
        }
        return $this->redirectToRoute(
            "administration_organisation_event_line_generate_nodika_start_generation",
            ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId(), "generation" => $generation->getId()]
        );
    }

    /**
     * @Route("/{generation}/confirm_generation", name="administration_organisation_event_line_generate_nodika_confirm_generation")
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
            'administration/organisation/event_line/generate/nodika/confirm_generation.html.twig', $arr
        );
    }

    /**
     * @Route("/{generation}/apply_generation", name="administration_organisation_event_line_generate_nodika_apply_generation")
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
        $resp = $generationService->persist($generation, $generationResult);
        if ($resp == EventGenerationServicePersistResponse::SUCCESSFUL) {
            return $this->redirectToRoute(
                "administration_organisation_event_line_administer",
                ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId()]
            );
        } else if ($resp == EventGenerationServicePersistResponse::MEMBER_NOT_FOUND_ANYMORE) {
            return $this->redirectToRoute(
                "administration_organisation_event_line_generate_nodika_choose_members",
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
     * @return NodikaConfiguration
     */
    private function getDistributionConfiguration(EventLineGeneration $generation, Organisation $organisation)
    {
        $configuration = new NodikaConfiguration(json_decode($generation->getDistributionConfigurationJson()));
        $this->addMemberConfiguration($configuration, $organisation);
        $this->addEventLineConfiguration($configuration, $organisation, $generation);
        $this->saveDistributionConfiguration($generation, $configuration);
        $this->fillHolidays($configuration);
        return $configuration;
    }

    /**
     * @param EventLineGeneration $generation
     * @param $configuration
     */
    private function saveDistributionConfiguration(EventLineGeneration $generation, NodikaConfiguration $configuration)
    {
        $generation->setDistributionConfiguration($configuration);
        $this->fastSave($generation);
    }


    /**
     * @param EventLineGeneration $generation
     * @param NodikaOutput $nodikaOutput
     */
    private function saveNodikaOutput(EventLineGeneration $generation, NodikaOutput $nodikaOutput)
    {
        $generation->setGenerationResult($nodikaOutput->generationResult);
        $generation->setDistributionOutput($nodikaOutput);
        $this->fastSave($generation);
    }

    /**
     * @param NodikaConfiguration $configuration
     * @param Organisation $organisation
     */
    private function addMemberConfiguration(NodikaConfiguration $configuration, Organisation $organisation)
    {
        /* @var Member[] $memberById */
        $memberById = [];
        foreach ($organisation->getMembers() as $member) {
            $memberById[$member->getId()] = $member;
        }

        $removeKeys = [];
        foreach ($configuration->memberConfigurations as $key => $value) {
            if (isset($memberById[$value->id])) {
                $value->name = $memberById[$value->id]->getName();
                unset($memberById[$value->id]);
            } else {
                $removeKeys[] = $key;
            }

        }

        $memberChanges = false;

        foreach ($removeKeys as $removeKey) {
            $memberChanges = true;
            unset($configuration->memberConfigurations[$removeKey]);
        }

        foreach ($memberById as $item) {
            $memberChanges = true;
            $newConfig = MemberConfiguration::createFromMember($item);
            $configuration->memberConfigurations[] = $newConfig;
        }

        if ($memberChanges) {
            $configuration->memberEventTypeDistributionFilled = false;
        }
    }

    /**
     * @param NodikaConfiguration $configuration
     */
    private function fillHolidays(NodikaConfiguration $configuration)
    {
        if (!$configuration->holidaysFilled) {
            $configuration->holidaysFilled = true;
            $currentYear = $configuration->startDateTime->format("Y");
            while (true) {
                $yearlyHolyDays = $this->getYearlyHolidays($currentYear);
                foreach ($yearlyHolyDays as $yearlyHolyDay) {
                    if ($yearlyHolyDay->getTimestamp() < $configuration->startDateTime->getTimestamp()) {
                        //too early
                    } else if ($yearlyHolyDay->getTimestamp() > $configuration->endDateTime->getTimestamp()) {
                        //too late; get back
                        return;
                    } else {
                        $configuration->holidays[] = $yearlyHolyDay;
                    }
                }
                $currentYear++;
            }
        }
    }

    /**
     * @param $year
     * @return \DateTime[]
     */
    private function getYearlyHolidays($year)
    {
        return [
            // new year: 01.01.2000
            new \DateTime("01.01." . $year),
            // noel: 25.12.2000
            new \DateTime("01.08." . $year),
            // swiss: 01.08.2000
            new \DateTime("25.12." . $year)
        ];
    }
}