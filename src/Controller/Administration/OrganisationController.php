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

use App\Controller\Base\BaseController;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Enum\ApplicationEventType;
use App\Enum\SubmitButtonType;
use App\Form\Organisation\OrganisationType;
use App\Helper\HashHelper;
use App\Model\Event\SearchEventModel;
use App\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/organisation")
 * @Security("has_role('ROLE_USER')")
 */
class OrganisationController extends BaseController
{
    /**
     * @Route("/new", name="administration_organisation_new")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $organisation = Organisation::createFromPerson($this->getPerson());
        $organisation->setActiveEnd(new \DateTime('today + 31 days'));
        $organisation->setIsActive(true);
        $organisation->addLeader($this->getPerson());
        $newOrganisationForm = $this->handleFormDoctrinePersist(
            $this->createCrudForm(OrganisationType::class, SubmitButtonType::CREATE),
            $request,
            $organisation,
            function ($form, $entity) use ($organisation) {
                return $this->redirectToRoute('administration_organisation_setup', ['organisation' => $organisation->getId()]);
            }
        );

        if ($newOrganisationForm instanceof Response) {
            return $newOrganisationForm;
        }

        $arr['new_organisation_form'] = $newOrganisationForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/new.html.twig',
            $arr,
            $this->generateUrl('dashboard_index')
        );
    }

    /**
     * @Route("/{organisation}/administer", name="administration_organisation_administer")
     *
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function administerAction(Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $setupStatus = $this->getDoctrine()->getRepository('App:Organisation')->getSetupStatus($organisation);
        if (!$setupStatus->getAllDone()) {
            $translator = $this->get('translator');
            $this->displayInfo(
                $translator->trans('messages.not_fully_setup', [], 'administration_organisation'),
                $this->generateUrl('administration_organisation_setup', ['organisation' => $organisation->getId()])
            );
        }

        $arr['organisation'] = $organisation;
        $setupStatus = $this->getDoctrine()->getRepository('App:Organisation')->getSetupStatus($organisation);

        return $this->renderWithBackUrl(
            'administration/organisation/administer.html.twig',
            $arr + ['organisation' => $organisation, 'setupStatus' => $setupStatus],
            $this->generateUrl('dashboard_index')
        );
    }

    /**
     * @Route("/{organisation}/edit", name="administration_organisation_edit")
     *
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $myForm = $this->handleCrudForm(
            $request,
            $organisation,
            SubmitButtonType::EDIT,
            function ($form, $entity) {
                return $this->redirectToRoute('member_view');
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }
        $arr['edit_form'] = $myForm->createView();

        return $this->renderWithBackUrl(
            'administration/organisation/edit.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_administer', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/setup", name="administration_organisation_setup")
     *
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function setupAction(Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $setupStatus = $this->getDoctrine()->getRepository('App:Organisation')->getSetupStatus($organisation);

        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/setup.html.twig',
            $arr + ['setupStatus' => $setupStatus],
            $this->generateUrl('administration_organisation_administer', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/members", name="administration_organisation_members")
     *
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function membersAction(Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/members.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_administer', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/members/invite", name="administration_organisation_members_invite")
     *
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function membersInviteAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository('App:OrganisationSetting')->getByOrganisation($organisation);

        if ('' === $organisationSetting->getMemberInviteEmailSubject()) {
            $organisationSetting->setMemberInviteEmailSubject(
                $this->get('translator')->trans('members_invite.email.default_subject', [], 'administration_organisation')
            );
        }
        if ('' === $organisationSetting->getMemberInviteEmailMessage()) {
            $organisationSetting->setMemberInviteEmailMessage(
                $this->get('translator')->trans('members_invite.email.default_message', [], 'administration_organisation')
            );
        }

        $hasPendingMember = false;
        foreach ($organisation->getMembers() as $member) {
            if (null === $member->getInvitationDateTime()) {
                $hasPendingMember = true;
            }
        }

        if ('POST' === $request->getMethod()) {
            $canForward = $hasPendingMember;
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'subject')) {
                    $organisationSetting->setMemberInviteEmailSubject($value);
                } elseif ('message' === $key) {
                    $organisationSetting->setMemberInviteEmailMessage($value);
                    if (!mb_strpos($value, 'LINK_REPLACE')) {
                        $this->get('translator')->trans('members_invite.error.no_link_replace_in_message', [], 'administration_organisation');
                        $canForward = false;
                    }
                }
            }
            $this->fastSave($organisationSetting);

            if ($canForward) {
                return $this->redirectToRoute('administration_organisation_members_invite_preview', ['organisation' => $organisation->getId()]);
            }
        }

        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/members_invite.html.twig',
            $arr +
            [
                'members' => $organisation->getMembers(),
                'subject' => $organisationSetting->getMemberInviteEmailSubject(),
                'message' => $organisationSetting->getMemberInviteEmailMessage(),
                'hasPendingMember' => $hasPendingMember,
            ],
            $this->generateUrl('administration_organisation_members', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/members/invite/preview", name="administration_organisation_members_invite_preview")
     *
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function membersInvitePreviewAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository('App:OrganisationSetting')->getByOrganisation($organisation);

        /* @var Member[] $notInvitedMembers */
        $notInvitedMembers = [];

        foreach ($organisation->getMembers() as $member) {
            if (null === $member->getInvitationDateTime()) {
                $notInvitedMembers[] = $member;
            }
        }

        if ('POST' === $request->getMethod()) {
            $variableMapping = [];
            foreach ($notInvitedMembers as $member) {
                $variableMapping[$member->getId()] = [];
                $variableMapping[$member->getId()]['FREE_1_REPLACE'] = '';
                $variableMapping[$member->getId()]['FREE_2_REPLACE'] = '';
                $variableMapping[$member->getId()]['FREE_3_REPLACE'] = '';
            }
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'free_1_')) {
                    $memberId = (int) mb_substr($key, 7); //to cut off free_1_
                    $variableMapping[$memberId]['FREE_1_REPLACE'] = $value;
                } elseif (0 === mb_strpos($key, 'free_2_')) {
                    $memberId = (int) mb_substr($key, 7); //to cut off free_2_
                    $variableMapping[$memberId]['FREE_2_REPLACE'] = $value;
                } elseif (0 === mb_strpos($key, 'free_3_')) {
                    $memberId = (int) mb_substr($key, 7); //to cut off free_3_
                    $variableMapping[$memberId]['FREE_3_REPLACE'] = $value;
                }
            }

            foreach ($notInvitedMembers as $member) {
                $subject = $organisationSetting->getMemberInviteEmailSubject();
                $body = $organisationSetting->getMemberInviteEmailMessage();

                $member->setInvitationHash(HashHelper::createNewResetHash());
                $member->setInvitationDateTime(new \DateTime());

                $variableMapping[$member->getId()]['LINK_REPLACE'] =
                    $this->generateUrl('access_invite', ['invitationHash' => $member->getInvitationHash()], UrlGeneratorInterface::ABSOLUTE_URL);
                $variableMapping[$member->getId()]['MEMBER_NAME_REPLACE'] = $member->getName();

                foreach ($variableMapping[$member->getId()] as $search => $replace) {
                    $subject = str_replace($search, $replace, $subject);
                    $body = str_replace($search, $replace, $body);
                }

                $this->get('app.email_service')->sendPlainEmail($member->getEmail(), $subject, $body);

                $this->fastSave($member);
            }

            $this->displaySuccess($this->get('translator')->trans('members_invite.successful.emails_send', ['%count%' => count($notInvitedMembers)], 'administration_organisation'));

            return $this->redirectToRoute('administration_organisation_members', ['organisation' => $organisation->getId()]);
        }

        $arr = [];

        $showFree1 =
            mb_strpos($organisationSetting->getMemberInviteEmailSubject(), 'FREE_1_REPLACE') ||
            mb_strpos($organisationSetting->getMemberInviteEmailMessage(), 'FREE_1_REPLACE');

        $showFree2 =
            mb_strpos($organisationSetting->getMemberInviteEmailSubject(), 'FREE_2_REPLACE') ||
            mb_strpos($organisationSetting->getMemberInviteEmailMessage(), 'FREE_2_REPLACE');

        $showFree3 =
            mb_strpos($organisationSetting->getMemberInviteEmailSubject(), 'FREE_3_REPLACE') ||
            mb_strpos($organisationSetting->getMemberInviteEmailMessage(), 'FREE_3_REPLACE');

        $arr['showFree1'] = $showFree1;
        $arr['showFree2'] = $showFree2;
        $arr['showFree3'] = $showFree3;
        $arr['organisation'] = $organisation;
        $arr['members'] = $notInvitedMembers;
        $arr['subject'] = $organisationSetting->getMemberInviteEmailSubject();
        $arr['message'] = $organisationSetting->getMemberInviteEmailMessage();

        return $this->renderWithBackUrl(
            'administration/organisation/members_invite_preview.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_members_invite', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/persons/invite", name="administration_organisation_persons_invite")
     *
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function personsInviteAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository('App:OrganisationSetting')->getByOrganisation($organisation);

        if ('' === $organisationSetting->getPersonInviteEmailSubject()) {
            $organisationSetting->setPersonInviteEmailSubject(
                $this->get('translator')->trans('persons_invite.email.default_subject', [], 'administration_organisation')
            );
        }
        if ('' === $organisationSetting->getPersonInviteEmailMessage()) {
            $organisationSetting->setPersonInviteEmailMessage(
                $this->get('translator')->trans('persons_invite.email.default_message', [], 'administration_organisation')
            );
        }

        $hasPendingPerson = false;
        $persons = [];
        foreach ($organisation->getMembers() as $member) {
            foreach ($member->getPersons() as $person) {
                if (null === $person->getFrontendUser()) {
                    $hasPendingPerson = true;
                }
                $persons[$person->getId()] = $person;
            }
        }

        if ('POST' === $request->getMethod()) {
            $canForward = $hasPendingPerson;
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'subject')) {
                    $organisationSetting->setPersonInviteEmailSubject($value);
                } elseif ('message' === $key) {
                    $organisationSetting->setPersonInviteEmailMessage($value);
                    if (!mb_strpos($value, 'LINK_REPLACE')) {
                        $this->get('translator')->trans('persons_invite.error.no_link_replace_in_message', [], 'administration_organisation');
                        $canForward = false;
                    }
                }
            }
            $this->fastSave($organisationSetting);

            if ($canForward) {
                return $this->redirectToRoute('administration_organisation_persons_invite_preview', ['organisation' => $organisation->getId()]);
            }
        }

        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/persons_invite.html.twig',
            $arr +
            [
                'persons' => $persons,
                'subject' => $organisationSetting->getPersonInviteEmailSubject(),
                'message' => $organisationSetting->getPersonInviteEmailMessage(),
                'hasPendingPerson' => $hasPendingPerson,
            ],
            $this->generateUrl('administration_organisation_members', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/persons/invite/preview", name="administration_organisation_persons_invite_preview")
     *
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function personsInvitePreviewAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository('App:OrganisationSetting')->getByOrganisation($organisation);

        /* @var Person[] $notInvitedPersons */
        $notInvitedPersons = [];
        foreach ($organisation->getMembers() as $member) {
            foreach ($member->getPersons() as $person) {
                if (!$person->getHasBeenInvited() && null === $person->getFrontendUser()) {
                    $notInvitedPersons[$person->getId()] = $person;
                }
            }
        }

        if ('POST' === $request->getMethod()) {
            $variableMapping = [];
            foreach ($notInvitedPersons as $person) {
                $variableMapping[$person->getId()] = [];
                $variableMapping[$person->getId()]['FREE_1_REPLACE'] = '';
                $variableMapping[$person->getId()]['FREE_2_REPLACE'] = '';
                $variableMapping[$person->getId()]['FREE_3_REPLACE'] = '';
            }
            foreach ($request->request->all() as $key => $value) {
                if (0 === mb_strpos($key, 'free_1_')) {
                    $personId = (int) mb_substr($key, 7); //to cut off free_1_
                    $variableMapping[$personId]['FREE_1_REPLACE'] = $value;
                } elseif (0 === mb_strpos($key, 'free_2_')) {
                    $personId = (int) mb_substr($key, 7); //to cut off free_2_
                    $variableMapping[$personId]['FREE_2_REPLACE'] = $value;
                } elseif (0 === mb_strpos($key, 'free_3_')) {
                    $personId = (int) mb_substr($key, 7); //to cut off free_3_
                    $variableMapping[$personId]['FREE_3_REPLACE'] = $value;
                }
            }

            foreach ($notInvitedPersons as $person) {
                $subject = $organisationSetting->getPersonInviteEmailSubject();
                $body = $organisationSetting->getPersonInviteEmailMessage();

                $person->setInvitationHash(HashHelper::createNewResetHash());
                $person->setInvitationDateTime(new \DateTime());

                $variableMapping[$person->getId()]['LINK_REPLACE'] =
                    $this->generateUrl('access_invite_person', ['invitationHash' => $person->getInvitationHash()], UrlGeneratorInterface::ABSOLUTE_URL);
                $variableMapping[$person->getId()]['PERSON_NAME_REPLACE'] = $person->getFullName();

                foreach ($variableMapping[$person->getId()] as $search => $replace) {
                    $subject = str_replace($search, $replace, $subject);
                    $body = str_replace($search, $replace, $body);
                }

                $this->get('app.email_service')->sendPlainEmail($person->getEmail(), $subject, $body);
                $this->fastSave($person);
            }

            $this->displaySuccess($this->get('translator')->trans('persons_invite.successful.emails_send', ['%count%' => count($notInvitedPersons)], 'administration_organisation'));

            return $this->redirectToRoute('administration_organisation_members', ['organisation' => $organisation->getId()]);
        }

        $arr = [];

        $showFree1 =
            mb_strpos($organisationSetting->getPersonInviteEmailSubject(), 'FREE_1_REPLACE') ||
            mb_strpos($organisationSetting->getPersonInviteEmailMessage(), 'FREE_1_REPLACE');

        $showFree2 =
            mb_strpos($organisationSetting->getPersonInviteEmailSubject(), 'FREE_2_REPLACE') ||
            mb_strpos($organisationSetting->getPersonInviteEmailMessage(), 'FREE_2_REPLACE');

        $showFree3 =
            mb_strpos($organisationSetting->getPersonInviteEmailSubject(), 'FREE_3_REPLACE') ||
            mb_strpos($organisationSetting->getPersonInviteEmailMessage(), 'FREE_3_REPLACE');

        $arr['showFree1'] = $showFree1;
        $arr['showFree2'] = $showFree2;
        $arr['showFree3'] = $showFree3;
        $arr['organisation'] = $organisation;
        $arr['persons'] = $notInvitedPersons;
        $arr['subject'] = $organisationSetting->getPersonInviteEmailSubject();
        $arr['message'] = $organisationSetting->getPersonInviteEmailMessage();

        return $this->renderWithBackUrl(
            'administration/organisation/persons_invite_preview.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_persons_invite', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/event_lines", name="administration_organisation_event_lines")
     *
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function eventLinesAction(Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/event_lines.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_administer', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/settings", name="administration_organisation_settings")
     *
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return Response
     */
    public function settingsAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $this->getDoctrine()->getRepository('App:ApplicationEvent')->registerEventOccurred($organisation, ApplicationEventType::VISITED_SETTINGS);
        $organisationSetting = $this->getDoctrine()->getRepository('App:OrganisationSetting')->getByOrganisation($organisation);

        $form = $this->handleCrudForm(
            $request,
            $organisationSetting,
            SubmitButtonType::EDIT
        );

        if ($form instanceof Response) {
            return $form;
        }

        $arr['settings_form'] = $form->createView();
        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/settings.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_administer', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/events", name="administration_organisation_events")
     *
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function eventsAction(Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $eventSearchModel = new SearchEventModel($organisation, new \DateTime());

        $eventLineModels = $this->getDoctrine()->getRepository('App:Organisation')->findEventLineModels($eventSearchModel);
        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/events.html.twig',
            $arr + ['eventLineModels' => $eventLineModels],
            $this->generateUrl('administration_organisation_administer', ['organisation' => $organisation->getId()])
        );
    }
}
