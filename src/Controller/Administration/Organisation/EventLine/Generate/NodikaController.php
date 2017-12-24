<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration\Organisation\EventLine\Generate;

use App\Controller\Administration\Organisation\EventLine\Generate\Base\BaseGenerationController;
use App\Entity\EventLine;
use App\Entity\EventLineGeneration;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Enum\DistributionType;
use App\Enum\EventGenerationServicePersistResponse;
use App\Enum\NodikaStatusCode;
use App\Form\EventLineGeneration\Nodika\ChoosePeriodType;
use App\Helper\DateTimeFormatter;
use App\Model\EventLineGeneration\Base\EventLineConfiguration;
use App\Model\EventLineGeneration\GenerationResult;
use App\Model\EventLineGeneration\Nodika\EventTypeConfiguration;
use App\Model\EventLineGeneration\Nodika\MemberConfiguration;
use App\Model\EventLineGeneration\Nodika\NodikaConfiguration;
use App\Model\EventLineGeneration\Nodika\NodikaOutput;
use App\Security\Voter\EventLineGenerationVoter;
use App\Security\Voter\EventLineVoter;
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
     *
     * @param Request      $request
     * @param Organisation $organisation
     * @param EventLine    $eventLine
     *
     * @return Response
     */
    public function newAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/nodika/new.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_choose', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()])
        );
    }

    /**
     * @Route("/start", name="administration_organisation_event_line_generate_nodika_start")
     *
     * @param Request      $request
     * @param Organisation $organisation
     * @param EventLine    $eventLine
     *
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
            'administration_organisation_event_line_generate_nodika_choose_period',
            ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $eventLineGeneration->getId()]
        );
    }

    /**
     * @Route("/{generation}/choose_period", name="administration_organisation_event_line_generate_nodika_choose_period")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function choosePeriodAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $form = $this->handleForm(
            $this->createForm(ChoosePeriodType::class),
            $request,
            $config,
            function ($form, $entity) use ($organisation, $eventLine, $generation, $config) {
                /* @var NodikaConfiguration $entity */
                $config->lengthInHours = $entity->lengthInHours;
                $config->startDateTime = $entity->startDateTime;
                $config->endDateTime = $entity->endDateTime;
                $config->memberEventTypeDistributionFilled = false;
                $this->saveDistributionConfiguration($generation, $config);

                return $this->redirectToRoute(
                    'administration_organisation_event_line_generate_nodika_no_conflicts',
                    ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
                );
            }
        );

        if ($form instanceof Response) {
            return $form;
        }

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;
        $arr['choosePeriodForm'] = $form->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/nodika/choose_period.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_nodika_new', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()])
        );
    }

    /**
     * @Route("/{generation}/no_conflicts", name="administration_organisation_event_line_generate_nodika_no_conflicts")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function noConflictsAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        if ('POST' === $request->getMethod()) {
            /* @var EventLineConfiguration[] $eventLineConfigurations */
            $eventLineConfigurations = [];
            foreach ($config->eventLineConfiguration as $eventLineConfiguration) {
                $eventLineConfigurations[$eventLineConfiguration->id] = $eventLineConfiguration;
            }
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'event_line_')) {
                    $eventLineId = mb_substr($key, 11); //cut off event_line_
                    if (isset($eventLineConfigurations[$eventLineId])) {
                        $eventLineConfigurations[$eventLineId]->isEnabled = true;
                    }
                } elseif ('conflict_puffer_in_hours' === $key) {
                    $config->conflictPufferInHours = $value;
                }
            }
            $config->eventLineConfiguration = $eventLineConfigurations;
            $config->memberEventTypeDistributionFilled = false;
            $this->saveDistributionConfiguration($generation, $config);

            return $this->redirectToRoute(
                'administration_organisation_event_line_generate_nodika_choose_members',
                ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
            );
        }

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['eventLineConfigurations'] = $config->eventLineConfiguration;
        $arr['conflictPufferInHours'] = $config->conflictPufferInHours;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/nodika/no_conflicts.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_nodika_choose_period', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/choose_members", name="administration_organisation_event_line_generate_nodika_choose_members")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function chooseMembersAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        if ('POST' === $request->getMethod()) {
            /* @var MemberConfiguration[] $memberConfigurations */
            $memberConfigurations = [];
            foreach ($config->memberConfigurations as $memberConfiguration) {
                $memberConfiguration->isEnabled = false;
                $memberConfigurations[$memberConfiguration->id] = $memberConfiguration;
            }
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'member_')) {
                    $memberId = mb_substr($key, 7); //cut off member_
                    if (isset($memberConfigurations[$memberId])) {
                        $memberConfigurations[$memberId]->isEnabled = true;
                    }
                }
            }
            $config->memberConfigurations = $memberConfigurations;
            $config->memberEventTypeDistributionFilled = false;
            $this->saveDistributionConfiguration($generation, $config);

            return $this->redirectToRoute(
                'administration_organisation_event_line_generate_nodika_relative_distribution',
                ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
            );
        }

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['memberConfigurations'] = $config->memberConfigurations;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/nodika/choose_members.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_nodika_no_conflicts', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/relative_distribution", name="administration_organisation_event_line_generate_nodika_relative_distribution")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function relativeDistributionAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        if ('POST' === $request->getMethod()) {
            /* @var MemberConfiguration[] $memberConfigurations */
            $memberConfigurations = [];
            foreach ($config->memberConfigurations as $memberConfiguration) {
                $memberConfigurations[$memberConfiguration->id] = $memberConfiguration;
            }
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'member_p_')) {
                    $memberId = mb_substr($key, 9); //cut off member_p_
                    if (isset($memberConfigurations[$memberId])) {
                        $memberConfigurations[$memberId]->points = $value;
                    }
                } elseif (0 === mb_strpos($key, 'member_l_')) {
                    $memberId = mb_substr($key, 9); //cut off member_l_
                    if (isset($memberConfigurations[$memberId])) {
                        $memberConfigurations[$memberId]->luckyScore = $value;
                    }
                }
            }
            $config->memberConfigurations = $memberConfigurations;
            $config->memberEventTypeDistributionFilled = false;
            $this->saveDistributionConfiguration($generation, $config);

            return $this->redirectToRoute(
                'administration_organisation_event_line_generate_nodika_distribution_settings',
                ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
            );
        }

        $onlyEnabled = [];
        foreach ($config->memberConfigurations as $memberConfiguration) {
            if ($memberConfiguration->isEnabled) {
                $onlyEnabled[] = $memberConfiguration;
            }
        }

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['memberConfigurations'] = $onlyEnabled;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/nodika/relative_distribution.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_nodika_choose_members', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/distribution_settings", name="administration_organisation_event_line_generate_nodika_distribution_settings")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function distributionSettingsAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $holidayString = '';
        foreach ($config->holidays as $holiday) {
            $holidayString .= $holiday->format(DateTimeFormatter::DATE_FORMAT).', ';
        }
        if (mb_strlen($holidayString) > 0) {
            $holidayString = mb_substr($holidayString, 0, -2);
        }

        if ('POST' === $request->getMethod()) {
            /* @var MemberConfiguration[] $memberConfigurations */
            $memberConfigurations = [];
            foreach ($config->memberConfigurations as $memberConfiguration) {
                $memberConfigurations[$memberConfiguration->id] = $memberConfiguration;
            }
            $submissionFailure = false;
            foreach ($request->request->all() as $key => $value) {
                if ('event_type_weekday' === $key) {
                    $config->eventTypeConfiguration->weekday = $value;
                } elseif ('event_type_saturday' === $key) {
                    $config->eventTypeConfiguration->saturday = $value;
                } elseif ('event_type_sunday' === $key) {
                    $config->eventTypeConfiguration->sunday = $value;
                } elseif ('event_type_holiday' === $key) {
                    $config->eventTypeConfiguration->holiday = $value;
                } elseif ('holiday_string' === $key) {
                    $holidayString = $value;
                    if ('' === trim($value)) {
                        $parts = [];
                    } elseif (!mb_strpos($value, ',')) {
                        $parts[] = $value;
                    } else {
                        $parts = explode(',', $value);
                    }
                    $sanitizedParts = [];
                    $foundInvalid = false;
                    foreach ($parts as $part) {
                        $part = trim($part);
                        //must be of the form dd.mm.yyyy
                        $parts = explode('.', $part);
                        if (!(3 === count($parts) &&
                            is_numeric($parts[0]) && 2 === mb_strlen($parts[0]) &&
                            is_numeric($parts[1]) && 2 === mb_strlen($parts[1]) &&
                            is_numeric($parts[2]) && 4 === mb_strlen($parts[2]))) {
                            $submissionFailure = true;
                            $foundInvalid = true;
                            $translator = $this->get('translator');
                            $this->displayError($translator->trans('error.date_format_invalid', ['%date%' => $part], 'administration_organisation_event_line_generate_nodika'));
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
                    'administration_organisation_event_line_generate_nodika_do_distribution',
                    ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
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
        $arr['organisation'] = $organisation;
        $arr['memberConfigurations'] = $onlyEnabled;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;
        $arr['eventTypeConfiguration'] = $config->eventTypeConfiguration;
        $arr['holidayString'] = $holidayString;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/nodika/distribution_settings.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_nodika_relative_distribution', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/do_distribution", name="administration_organisation_event_line_generate_nodika_do_distribution")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function doDistributionAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $generationService = $this->get('app.event_generation_service');
        $generationService->setEventTypeDistribution($config);
        $config->memberEventTypeDistributionFilled = true;
        $this->saveDistributionConfiguration($generation, $config);

        return $this->redirectToRoute(
            'administration_organisation_event_line_generate_nodika_distribution_confirm',
            ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
        );
    }

    /**
     * @Route("/{generation}/distribution_confirm", name="administration_organisation_event_line_generate_nodika_distribution_confirm")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function distributionConfirmAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $eventTypeAssignment = new EventTypeConfiguration(null);
        foreach ($config->memberEventTypeDistributions as $distribution) {
            $eventTypeAssignment->holiday += $distribution->eventTypeAssignment->holiday;
            $eventTypeAssignment->sunday += $distribution->eventTypeAssignment->sunday;
            $eventTypeAssignment->saturday += $distribution->eventTypeAssignment->saturday;
            $eventTypeAssignment->weekday += $distribution->eventTypeAssignment->weekday;
        }

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;
        $arr['memberEventTypeDistributions'] = $config->memberEventTypeDistributions;
        $arr['eventTypeAssignmentTotal'] = $eventTypeAssignment;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/nodika/distribution_confirm.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_nodika_distribution_settings', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/assignment_settings", name="administration_organisation_event_line_generate_nodika_assignment_settings")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function assignmentSettingsAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $beforeEventsString = '';
        foreach ($config->beforeEvents as $beforeEvent) {
            $beforeEventsString .= $beforeEvent.', ';
        }
        if (mb_strlen($beforeEventsString) > 0) {
            $beforeEventsString = mb_substr($beforeEventsString, 0, -2);
        }

        if ('POST' === $request->getMethod()) {
            $translator = $this->get('translator');
            /* @var MemberConfiguration[] $memberConfigurations */
            $memberConfigurations = [];
            foreach ($config->memberConfigurations as $memberConfiguration) {
                $memberConfigurations[$memberConfiguration->id] = $memberConfiguration;
            }
            $submissionFailure = false;
            foreach ($request->request->all() as $key => $value) {
                if ('before_events' === $key) {
                    $beforeEventsString = $value;
                    if ('' === trim($value)) {
                        $parts = [];
                    } elseif (!mb_strpos($value, ',')) {
                        $parts[] = $value;
                    } else {
                        $parts = explode(',', $value);
                    }
                    $sanitizedParts = [];
                    $foundInvalid = false;
                    foreach ($parts as $part) {
                        $part = trim($part);
                        if (!is_numeric($part)) {
                            $submissionFailure = true;
                            $foundInvalid = true;
                            $this->displayError($translator->trans('error.member_format_invalid', ['%part%' => $part], 'administration_organisation_event_line_generate_nodika'));
                        } else {
                            $sanitizedParts[] = $part;
                        }
                    }
                    if (!$foundInvalid) {
                        foreach ($sanitizedParts as $sanitizedPart) {
                            if (0 !== $sanitizedPart && !isset($memberConfigurations[$sanitizedPart])) {
                                $this->displayError($translator->trans('error.member_not_found', ['%id%' => $sanitizedPart], 'administration_organisation_event_line_generate_nodika'));
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
                    'administration_organisation_event_line_generate_nodika_start_generation',
                    ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
                );
            }
        }

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;
        $arr['memberConfigurations'] = $config->memberConfigurations;
        $arr['beforeEventsString'] = $beforeEventsString;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/nodika/assignment_settings.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_nodika_distribution_confirm', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/start_generation", name="administration_organisation_event_line_generate_nodika_start_generation")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function startGenerationAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/nodika/start_generation.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_nodika_assignment_settings', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/do_generate", name="administration_organisation_event_line_generate_nodika_do_generate")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function doGenerationAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        /* @var NodikaOutput $nodikaOutput */
        $nodikaOutput = $this->get('app.event_generation_service')->generateNodika(
            $config,
            function ($startDate, $endDate, $assignedEventCount, $member) {
                return true;
            }
        );
        if ($nodikaOutput instanceof NodikaOutput) {
            if (NodikaStatusCode::SUCCESSFUL === $nodikaOutput->nodikaStatusCode) {
                $this->saveNodikaOutput($generation, $nodikaOutput);

                return $this->redirectToRoute(
                    'administration_organisation_event_line_generate_nodika_confirm_generation',
                    ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
                );
            }
        } else {
            $translator = $this->get('translator');
            $this->displayError(
                $translator->trans(
                    NodikaStatusCode::getTranslation(NodikaStatusCode::UNKNOWN_ERROR),
                    [],
                    NodikaStatusCode::getTranslationDomainStatic()
                )
            );
        }

        return $this->redirectToRoute(
            'administration_organisation_event_line_generate_nodika_start_generation',
            ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
        );
    }

    /**
     * @Route("/{generation}/confirm_generation", name="administration_organisation_event_line_generate_nodika_confirm_generation")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function confirmGenerationAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $generationResult = $this->getGenerationResult($generation);

        $memberById = [];
        foreach ($this->getDoctrine()->getRepository('App:Member')->findBy(['organisation' => $organisation->getId()]) as $item) {
            $memberById[$item->getId()] = $item;
        }

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;
        $arr['generationResult'] = $generationResult;
        $arr['memberById'] = $memberById;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/nodika/confirm_generation.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_nodika_start_generation', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/apply_generation", name="administration_organisation_event_line_generate_nodika_apply_generation")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function applyGenerationAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $generationResult = $this->getGenerationResult($generation);

        $generationService = $this->get('app.event_generation_service');
        $resp = $generationService->persist($generation, $generationResult, $this->getPerson());
        if (EventGenerationServicePersistResponse::SUCCESSFUL === $resp) {
            return $this->redirectToRoute(
                'administration_organisation_event_line_administer',
                ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()]
            );
        } elseif (EventGenerationServicePersistResponse::MEMBER_NOT_FOUND_ANYMORE === $resp) {
            return $this->redirectToRoute(
                'administration_organisation_event_line_generate_nodika_choose_members',
                ['organisation' => $generation->getEventLine()->getOrganisation()->getId(), 'eventLine' => $generation->getEventLine()->getId(), 'generation' => $generation->getId()]
            );
        }

        return $this->redirectToRoute(
                'administration_organisation_event_line_generate_nodika_confirm_generation',
                ['organisation' => $generation->getEventLine()->getOrganisation()->getId(), 'eventLine' => $generation->getEventLine()->getId(), 'generation' => $generation->getId()]
            );
    }

    /**
     * @param EventLineGeneration $generation
     * @param Organisation        $organisation
     *
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
     * @param NodikaOutput        $nodikaOutput
     */
    private function saveNodikaOutput(EventLineGeneration $generation, NodikaOutput $nodikaOutput)
    {
        $generation->setGenerationResult($nodikaOutput->generationResult);
        $generation->setDistributionOutput($nodikaOutput);
        $this->fastSave($generation);
    }

    /**
     * @param NodikaConfiguration $configuration
     * @param Organisation        $organisation
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
            $currentYear = $configuration->startDateTime->format('Y');
            while (true) {
                $yearlyHolyDays = $this->getYearlyHolidays($currentYear);
                foreach ($yearlyHolyDays as $yearlyHolyDay) {
                    if ($yearlyHolyDay->getTimestamp() < $configuration->startDateTime->getTimestamp()) {
                        //too early
                    } elseif ($yearlyHolyDay->getTimestamp() > $configuration->endDateTime->getTimestamp()) {
                        //too late; get back
                        return;
                    } else {
                        $configuration->holidays[] = $yearlyHolyDay;
                    }
                }
                ++$currentYear;
            }
        }
    }

    /**
     * @param $year
     *
     * @return \DateTime[]
     */
    private function getYearlyHolidays($year)
    {
        return [
            // new year: 01.01.2000
            new \DateTime('01.01.'.$year),
            // noel: 25.12.2000
            new \DateTime('01.08.'.$year),
            // swiss: 01.08.2000
            new \DateTime('25.12.'.$year),
        ];
    }
}
