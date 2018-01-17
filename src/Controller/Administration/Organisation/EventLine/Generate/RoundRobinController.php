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
use App\Enum\RoundRobinStatusCode;
use App\Form\EventLineGeneration\RoundRobin\ChoosePeriodType;
use App\Model\EventLineGeneration\Base\EventLineConfiguration;
use App\Model\EventLineGeneration\GenerationResult;
use App\Model\EventLineGeneration\RoundRobin\MemberConfiguration;
use App\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use App\Model\EventLineGeneration\RoundRobin\RoundRobinOutput;
use App\Security\Voter\EventLineGenerationVoter;
use App\Security\Voter\EventLineVoter;
use App\Service\EventGenerationService;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/round_robin")
 * @Security("has_role('ROLE_USER')")
 */
class RoundRobinController extends BaseGenerationController
{
    /**
     * @Route("/new", name="administration_organisation_event_line_generate_round_robin_new")
     *
     * @param Organisation $organisation
     * @param EventLine    $eventLine
     *
     * @return Response
     */
    public function newAction(Organisation $organisation, EventLine $eventLine)
    {
        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/round_robin/new.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_choose', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()])
        );
    }

    /**
     * @Route("/start", name="administration_organisation_event_line_generate_round_robin_start")
     *
     * @param Organisation $organisation
     * @param EventLine    $eventLine
     *
     * @return Response
     */
    public function startAction(Organisation $organisation, EventLine $eventLine)
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
            'administration_organisation_event_line_generate_round_robin_choose_period',
            ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $eventLineGeneration->getId()]
        );
    }

    /**
     * @Route("/{generation}/choose_period", name="administration_organisation_event_line_generate_round_robin_choose_period")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     *
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function choosePeriodAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $form = $this->handleForm(
            $this->createForm(ChoosePeriodType::class),
            $request,
            $translator,
            $config,
            function ($form, $entity) use ($organisation, $eventLine, $generation, $config) {
                /* @var RoundRobinConfiguration $entity */
                $config->lengthInHours = $entity->lengthInHours;
                $config->startDateTime = $entity->startDateTime;
                $config->endDateTime = $entity->endDateTime;
                $this->saveDistributionConfiguration($generation, $config);

                return $this->redirectToRoute(
                    'administration_organisation_event_line_generate_round_robin_no_conflicts',
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
            'administration/organisation/event_line/generate/round_robin/choose_period.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()])
        );
    }

    /**
     * @Route("/{generation}/no_conflicts", name="administration_organisation_event_line_generate_round_robin_no_conflicts")
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
            $this->saveDistributionConfiguration($generation, $config);

            return $this->redirectToRoute(
                'administration_organisation_event_line_generate_round_robin_choose_members',
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
            'administration/organisation/event_line/generate/round_robin/no_conflicts.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_round_robin_choose_period', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/choose_members", name="administration_organisation_event_line_generate_round_robin_choose_members")
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
            $this->saveDistributionConfiguration($generation, $config);

            return $this->redirectToRoute(
                'administration_organisation_event_line_generate_round_robin_set_order',
                ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
            );
        }

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['memberConfigurations'] = $config->memberConfigurations;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/round_robin/choose_members.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_round_robin_choose_period', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/randomize_member_order", name="administration_organisation_event_line_generate_round_robin_randomize_member_order")
     *
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function randomizeMemberOrderAction(Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        $this->randomizeMemberOrder($config);
        $this->saveDistributionConfiguration($generation, $config);

        return $this->redirectToRoute(
            'administration_organisation_event_line_generate_round_robin_set_order',
            ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
        );
    }

    /**
     * @Route("/{generation}/set_order", name="administration_organisation_event_line_generate_round_robin_set_order")
     *
     * @param Request             $request
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function setOrderAction(Request $request, Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
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
                if (0 === mb_strpos($key, 'member_')) {
                    $memberId = mb_substr($key, 7); //cut off member_
                    if (isset($memberConfigurations[$memberId])) {
                        $memberConfigurations[$memberId]->order = $value;
                    }
                }
            }

            //first sort by order
            $ordered = [];
            $count = 1;
            foreach ($memberConfigurations as $memberConfiguration) {
                $ordered[$memberConfiguration->order][] = $memberConfiguration;
            }
            ksort($ordered);

            //collapse array & normalize order
            $memberConfigurations = [];
            foreach ($ordered as $orderEntry) {
                foreach ($orderEntry as $entry) {
                    $entry->order = $count++;
                    $memberConfigurations[] = $entry;
                }
            }
            $config->memberConfigurations = $memberConfigurations;
            $this->saveDistributionConfiguration($generation, $config);

            return $this->redirectToRoute(
                'administration_organisation_event_line_generate_round_robin_start_generation',
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
            'administration/organisation/event_line/generate/round_robin/set_order.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_round_robin_choose_members', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/start_generation", name="administration_organisation_event_line_generate_round_robin_start_generation")
     *
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function startGenerationAction(Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);

        $arr = [];
        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;
        $arr['eventLineGeneration'] = $generation;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/round_robin/start_generation.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_round_robin_set_order', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/do_generate", name="administration_organisation_event_line_generate_round_robin_do_generate")
     *
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     * @param TranslatorInterface $translator
     * @param EventGenerationService $eventGenerationService
     * @param LoggerInterface $logger
     * @return Response
     * @internal param Request $request
     */
    public function doGenerationAction(Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation, TranslatorInterface $translator, EventGenerationService $eventGenerationService, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $config = $this->getDistributionConfiguration($generation, $organisation);

        /* @var RoundRobinOutput $roundRobinOutput */
        $roundRobinOutput = $eventGenerationService->generateRoundRobin(
            $config,
            function ($startDate, $endDate, $assignedEventCount, $member) {
                return true;
            }
        );
        if ($roundRobinOutput instanceof RoundRobinOutput) {
            if (RoundRobinStatusCode::SUCCESSFUL === $roundRobinOutput->roundRobinStatusCode) {
                $this->saveRoundRobinOutput($generation, $roundRobinOutput);

                return $this->redirectToRoute(
                    'administration_organisation_event_line_generate_round_robin_confirm_generation',
                    ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
                );
            }
            $logger->log(Logger::ERROR, 'round robin error occurred with generation id '.$generation->getId());
        } else {
            $this->displayError(
                $translator->trans(
                    RoundRobinStatusCode::getTranslation(RoundRobinStatusCode::UNKNOWN_ERROR),
                    [],
                    RoundRobinStatusCode::getTranslationDomainStatic()
                )
            );
        }

        return $this->redirectToRoute(
            'administration_organisation_event_line_generate_round_robin_start_generation',
            ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()]
        );
    }

    /**
     * @Route("/{generation}/confirm_generation", name="administration_organisation_event_line_generate_round_robin_confirm_generation")
     *
     * @param Organisation        $organisation
     * @param EventLine           $eventLine
     * @param EventLineGeneration $generation
     *
     * @return Response
     */
    public function confirmGenerationAction(Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation)
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
            'administration/organisation/event_line/generate/round_robin/confirm_generation.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_generate_round_robin_start_generation', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId(), 'generation' => $generation->getId()])
        );
    }

    /**
     * @Route("/{generation}/apply_generation", name="administration_organisation_event_line_generate_round_robin_apply_generation")
     *
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @param EventLineGeneration $generation
     *
     * @param EventGenerationService $eventGenerationService
     * @return Response
     */
    public function applyGenerationAction(Organisation $organisation, EventLine $eventLine, EventLineGeneration $generation, EventGenerationService $eventGenerationService)
    {
        $this->denyAccessUnlessGranted(EventLineGenerationVoter::ADMINISTRATE, $generation);
        $generationResult = $this->getGenerationResult($generation);

        $resp = $eventGenerationService->persist($generation, $generationResult, $this->getPerson());
        if (EventGenerationServicePersistResponse::SUCCESSFUL === $resp) {
            return $this->redirectToRoute(
                'administration_organisation_event_line_administer',
                ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()]
            );
        } elseif (EventGenerationServicePersistResponse::MEMBER_NOT_FOUND_ANYMORE === $resp) {
            return $this->redirectToRoute(
                'administration_organisation_event_line_generate_round_robin_choose_members',
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
     * @param RoundRobinOutput    $roundRobinOutput
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
     * @param Organisation            $organisation
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
