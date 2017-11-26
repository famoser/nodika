<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 14:50
 */

namespace AppBundle\Controller\Administration;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Person;
use AppBundle\Enum\ApplicationEventType;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\Organisation\OrganisationType;
use AppBundle\Helper\HashHelper;
use AppBundle\Model\Event\SearchEventModel;
use AppBundle\Security\Voter\OrganisationVoter;
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
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $organisation = Organisation::createFromPerson($this->getPerson());
        $organisation->setActiveEnd(new \DateTime("today + 31 days"));
        $organisation->setIsActive(true);
        $organisation->addLeader($this->getPerson());
        $newOrganisationForm = $this->handleFormDoctrinePersist(
            $this->createCrudForm(OrganisationType::class, SubmitButtonType::CREATE),
            $request,
            $organisation,
            function ($form, $entity) use ($organisation) {
                return $this->redirectToRoute("administration_organisation_setup", ["organisation" => $organisation->getId()]);
            }
        );

        if ($newOrganisationForm instanceof Response) {
            return $newOrganisationForm;
        }

        $arr["new_organisation_form"] = $newOrganisationForm->createView();
        return $this->renderWithBackUrl(
            'administration/organisation/new.html.twig', $arr, $this->generateUrl("dashboard_index")
        );
    }

    /**
     * @Route("/{organisation}/administer", name="administration_organisation_administer")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function administerAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $setupStatus = $this->getDoctrine()->getRepository("AppBundle:Organisation")->getSetupStatus($organisation);
        if (!$setupStatus->getAllDone()) {
            $translator = $this->get("translator");
            $this->displayInfo(
                $translator->trans("messages.not_fully_setup", [], "administration_organisation"),
                $this->generateUrl("administration_organisation_setup", ["organisation" => $organisation->getId()])
            );
        }

        $arr["organisation"] = $organisation;
        $setupStatus = $this->getDoctrine()->getRepository("AppBundle:Organisation")->getSetupStatus($organisation);
        return $this->renderWithBackUrl(
            'administration/organisation/administer.html.twig',
            $arr + ["organisation" => $organisation, "setupStatus" => $setupStatus],
            $this->generateUrl("dashboard_index")
        );
    }

    /**
     * @Route("/{organisation}/edit", name="administration_organisation_edit")
     * @param Request $request
     * @param Organisation $organisation
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
                return $this->redirectToRoute("member_view");
            }
        );

        if ($myForm instanceof Response) {
            return $myForm;
        }
        $arr["edit_form"] = $myForm->createView();
        return $this->renderWithBackUrl(
            'administration/organisation/edit.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_administer", ["organisation" => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/setup", name="administration_organisation_setup")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function setupAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $setupStatus = $this->getDoctrine()->getRepository("AppBundle:Organisation")->getSetupStatus($organisation);

        $arr["organisation"] = $organisation;
        return $this->renderWithBackUrl(
            'administration/organisation/setup.html.twig',
            $arr + ["setupStatus" => $setupStatus],
            $this->generateUrl("administration_organisation_administer", ["organisation" => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/members", name="administration_organisation_members")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function membersAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $arr["organisation"] = $organisation;
        return $this->renderWithBackUrl(
            'administration/organisation/members.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_administer", ["organisation" => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/members/invite", name="administration_organisation_members_invite")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function membersInviteAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository("AppBundle:OrganisationSetting")->getByOrganisation($organisation);

        if ($organisationSetting->getMemberInviteEmailSubject() == "") {
            $organisationSetting->setMemberInviteEmailSubject(
                $this->get("translator")->trans("members_invite.email.default_subject", [], "administration_organisation")
            );
        }
        if ($organisationSetting->getMemberInviteEmailMessage() == "") {
            $organisationSetting->setMemberInviteEmailMessage(
                $this->get("translator")->trans("members_invite.email.default_message", [], "administration_organisation")
            );
        }


        $hasPendingMember = false;
        foreach ($organisation->getMembers() as $member) {
            if ($member->getInvitationDateTime() == null) {
                $hasPendingMember = true;
            }
        }

        if ($request->getMethod() == "POST") {
            $canForward = $hasPendingMember;
            foreach ($request->request->all() as $key => $value) {
                if (strpos($key, "subject") === 0) {
                    $organisationSetting->setMemberInviteEmailSubject($value);
                } else if ($key == "message") {
                    $organisationSetting->setMemberInviteEmailMessage($value);
                    if (!strpos($value, "LINK_REPLACE")) {
                        $this->get("translator")->trans("members_invite.error.no_link_replace_in_message", [], "administration_organisation");
                        $canForward = false;
                    }
                }
            }
            $this->fastSave($organisationSetting);

            if ($canForward) {
                return $this->redirectToRoute("administration_organisation_members_invite_preview", ["organisation" => $organisation->getId()]);
            }
        }

        $arr["organisation"] = $organisation;
        return $this->renderWithBackUrl(
            'administration/organisation/members_invite.html.twig',
            $arr +
            [
                "members" => $organisation->getMembers(),
                "subject" => $organisationSetting->getMemberInviteEmailSubject(),
                "message" => $organisationSetting->getMemberInviteEmailMessage(),
                "hasPendingMember" => $hasPendingMember
            ],
            $this->generateUrl("administration_organisation_members", ["organisation" => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/members/invite/preview", name="administration_organisation_members_invite_preview")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function membersInvitePreviewAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository("AppBundle:OrganisationSetting")->getByOrganisation($organisation);


        /* @var Member[] $notInvitedMembers */
        $notInvitedMembers = [];

        foreach ($organisation->getMembers() as $member) {
            if ($member->getInvitationDateTime() == null) {
                $notInvitedMembers[] = $member;
            }
        }


        if ($request->getMethod() == "POST") {
            $variableMapping = [];
            foreach ($notInvitedMembers as $member) {
                $variableMapping[$member->getId()] = [];
                $variableMapping[$member->getId()]["FREE_1_REPLACE"] = "";
                $variableMapping[$member->getId()]["FREE_2_REPLACE"] = "";
                $variableMapping[$member->getId()]["FREE_3_REPLACE"] = "";
            }
            foreach ($request->request->all() as $key => $value) {
                if (strpos($key, "free_1_") === 0) {
                    $memberId = (int)substr($key, 7); //to cut off free_1_
                    $variableMapping[$memberId]["FREE_1_REPLACE"] = $value;
                } else if (strpos($key, "free_2_") === 0) {
                    $memberId = (int)substr($key, 7); //to cut off free_2_
                    $variableMapping[$memberId]["FREE_2_REPLACE"] = $value;
                } else if (strpos($key, "free_3_") === 0) {
                    $memberId = (int)substr($key, 7); //to cut off free_3_
                    $variableMapping[$memberId]["FREE_3_REPLACE"] = $value;
                }
            }

            foreach ($notInvitedMembers as $member) {
                $subject = $organisationSetting->getMemberInviteEmailSubject();
                $body = $organisationSetting->getMemberInviteEmailMessage();

                $member->setInvitationHash(HashHelper::createNewResetHash());
                $member->setInvitationDateTime(new \DateTime());

                $variableMapping[$member->getId()]["LINK_REPLACE"] =
                    $this->generateUrl("access_invite", ["invitationHash" => $member->getInvitationHash()], UrlGeneratorInterface::ABSOLUTE_URL);
                $variableMapping[$member->getId()]["MEMBER_NAME_REPLACE"] = $member->getName();


                foreach ($variableMapping[$member->getId()] as $search => $replace) {
                    $subject = str_replace($search, $replace, $subject);
                    $body = str_replace($search, $replace, $body);
                }


                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($this->getParameter("mailer_email"))
                    ->setTo($member->getEmail())
                    ->setBody($body);
                $this->get('mailer')->send($message);

                $this->fastSave($member);
            }

            $this->displaySuccess($this->get("translator")->trans("members_invite.successful.emails_send", ["%count%" => count($notInvitedMembers)], "administration_organisation"));

            return $this->redirectToRoute("administration_organisation_members", ["organisation" => $organisation->getId()]);
        }

        $arr = [];

        $showFree1 =
            strpos($organisationSetting->getMemberInviteEmailSubject(), "FREE_1_REPLACE") ||
            strpos($organisationSetting->getMemberInviteEmailMessage(), "FREE_1_REPLACE");

        $showFree2 =
            strpos($organisationSetting->getMemberInviteEmailSubject(), "FREE_2_REPLACE") ||
            strpos($organisationSetting->getMemberInviteEmailMessage(), "FREE_2_REPLACE");

        $showFree3 =
            strpos($organisationSetting->getMemberInviteEmailSubject(), "FREE_3_REPLACE") ||
            strpos($organisationSetting->getMemberInviteEmailMessage(), "FREE_3_REPLACE");

        $arr["showFree1"] = $showFree1;
        $arr["showFree2"] = $showFree2;
        $arr["showFree3"] = $showFree3;
        $arr["organisation"] = $organisation;
        $arr["members"] = $notInvitedMembers;
        $arr["subject"] = $organisationSetting->getMemberInviteEmailSubject();
        $arr["message"] = $organisationSetting->getMemberInviteEmailMessage();
        return $this->renderWithBackUrl(
            'administration/organisation/members_invite_preview.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_members_invite", ["organisation" => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/persons/invite", name="administration_organisation_persons_invite")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function personsInviteAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository("AppBundle:OrganisationSetting")->getByOrganisation($organisation);

        if ($organisationSetting->getPersonInviteEmailSubject() == "") {
            $organisationSetting->setPersonInviteEmailSubject(
                $this->get("translator")->trans("persons_invite.email.default_subject", [], "administration_organisation")
            );
        }
        if ($organisationSetting->getPersonInviteEmailMessage() == "") {
            $organisationSetting->setPersonInviteEmailMessage(
                $this->get("translator")->trans("persons_invite.email.default_message", [], "administration_organisation")
            );
        }


        $hasPendingPerson = false;
        $persons = [];
        foreach ($organisation->getMembers() as $member) {
            foreach ($member->getPersons() as $person) {
                if ($person->getFrontendUser() == null) {
                    $hasPendingPerson = true;
                }
                $persons[$person->getId()] = $person;
            }
        }

        if ($request->getMethod() == "POST") {
            $canForward = $hasPendingPerson;
            foreach ($request->request->all() as $key => $value) {
                if (strpos($key, "subject") === 0) {
                    $organisationSetting->setPersonInviteEmailSubject($value);
                } else if ($key == "message") {
                    $organisationSetting->setPersonInviteEmailMessage($value);
                    if (!strpos($value, "LINK_REPLACE")) {
                        $this->get("translator")->trans("persons_invite.error.no_link_replace_in_message", [], "administration_organisation");
                        $canForward = false;
                    }
                }
            }
            $this->fastSave($organisationSetting);

            if ($canForward) {
                return $this->redirectToRoute("administration_organisation_persons_invite_preview", ["organisation" => $organisation->getId()]);
            }
        }

        $arr["organisation"] = $organisation;
        return $this->renderWithBackUrl(
            'administration/organisation/persons_invite.html.twig',
            $arr +
            [
                "persons" => $persons,
                "subject" => $organisationSetting->getPersonInviteEmailSubject(),
                "message" => $organisationSetting->getPersonInviteEmailMessage(),
                "hasPendingPerson" => $hasPendingPerson
            ],
            $this->generateUrl("administration_organisation_members", ["organisation" => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/persons/invite/preview", name="administration_organisation_persons_invite_preview")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function personsInvitePreviewAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $organisationSetting = $this->getDoctrine()->getRepository("AppBundle:OrganisationSetting")->getByOrganisation($organisation);


        /* @var Person[] $notInvitedPersons */
        $notInvitedPersons = [];
        foreach ($organisation->getMembers() as $member) {
            foreach ($member->getPersons() as $person) {
                if ($person->getFrontendUser() == null) {
                    $notInvitedPersons[$person->getId()] = $person;
                }
            }
        }


        if ($request->getMethod() == "POST") {
            $variableMapping = [];
            foreach ($notInvitedPersons as $person) {
                $variableMapping[$person->getId()] = [];
                $variableMapping[$person->getId()]["FREE_1_REPLACE"] = "";
                $variableMapping[$person->getId()]["FREE_2_REPLACE"] = "";
                $variableMapping[$person->getId()]["FREE_3_REPLACE"] = "";
            }
            foreach ($request->request->all() as $key => $value) {
                if (strpos($key, "free_1_") === 0) {
                    $personId = (int)substr($key, 7); //to cut off free_1_
                    $variableMapping[$personId]["FREE_1_REPLACE"] = $value;
                } else if (strpos($key, "free_2_") === 0) {
                    $personId = (int)substr($key, 7); //to cut off free_2_
                    $variableMapping[$personId]["FREE_2_REPLACE"] = $value;
                } else if (strpos($key, "free_3_") === 0) {
                    $personId = (int)substr($key, 7); //to cut off free_3_
                    $variableMapping[$personId]["FREE_3_REPLACE"] = $value;
                }
            }

            foreach ($notInvitedPersons as $person) {
                $subject = $organisationSetting->getPersonInviteEmailSubject();
                $body = $organisationSetting->getPersonInviteEmailMessage();

                $person->setInvitationHash(HashHelper::createNewResetHash());
                $person->setInvitationDateTime(new \DateTime());

                $variableMapping[$person->getId()]["LINK_REPLACE"] =
                    $this->generateUrl("access_invite_person", ["invitationHash" => $person->getInvitationHash()], UrlGeneratorInterface::ABSOLUTE_URL);
                $variableMapping[$person->getId()]["PERSON_NAME_REPLACE"] = $person->getFullName();


                foreach ($variableMapping[$person->getId()] as $search => $replace) {
                    $subject = str_replace($search, $replace, $subject);
                    $body = str_replace($search, $replace, $body);
                }


                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($this->getParameter("mailer_email"))
                    ->setTo($person->getEmail())
                    ->setBody($body);

                $this->get('mailer')->send($message);

                $this->fastSave($person);
            }

            $this->displaySuccess($this->get("translator")->trans("persons_invite.successful.emails_send", ["%count%" => count($notInvitedPersons)], "administration_organisation"));

            return $this->redirectToRoute("administration_organisation_members", ["organisation" => $organisation->getId()]);
        }

        $arr = [];

        $showFree1 =
            strpos($organisationSetting->getPersonInviteEmailSubject(), "FREE_1_REPLACE") ||
            strpos($organisationSetting->getPersonInviteEmailMessage(), "FREE_1_REPLACE");

        $showFree2 =
            strpos($organisationSetting->getPersonInviteEmailSubject(), "FREE_2_REPLACE") ||
            strpos($organisationSetting->getPersonInviteEmailMessage(), "FREE_2_REPLACE");

        $showFree3 =
            strpos($organisationSetting->getPersonInviteEmailSubject(), "FREE_3_REPLACE") ||
            strpos($organisationSetting->getPersonInviteEmailMessage(), "FREE_3_REPLACE");

        $arr["showFree1"] = $showFree1;
        $arr["showFree2"] = $showFree2;
        $arr["showFree3"] = $showFree3;
        $arr["organisation"] = $organisation;
        $arr["persons"] = $notInvitedPersons;
        $arr["subject"] = $organisationSetting->getPersonInviteEmailSubject();
        $arr["message"] = $organisationSetting->getPersonInviteEmailMessage();
        return $this->renderWithBackUrl(
            'administration/organisation/persons_invite_preview.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_persons_invite", ["organisation" => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/event_lines", name="administration_organisation_event_lines")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function eventLinesAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $arr["organisation"] = $organisation;
        return $this->renderWithBackUrl(
            'administration/organisation/event_lines.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_administer", ["organisation" => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/settings", name="administration_organisation_settings")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function settingsAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $this->getDoctrine()->getRepository("AppBundle:ApplicationEvent")->registerEventOccurred($organisation, ApplicationEventType::VISITED_SETTINGS);
        $organisationSetting = $this->getDoctrine()->getRepository("AppBundle:OrganisationSetting")->getByOrganisation($organisation);

        $form = $this->handleCrudForm(
            $request,
            $organisationSetting,
            SubmitButtonType::EDIT
        );

        if ($form instanceof Response) {
            return $form;
        }

        $arr["settings_form"] = $form->createView();
        $arr["organisation"] = $organisation;
        return $this->renderWithBackUrl(
            'administration/organisation/settings.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_administer", ["organisation" => $organisation->getId()])
        );
    }

    /**
     * @Route("/{organisation}/events", name="administration_organisation_events")
     * @param Request $request
     * @param Organisation $organisation
     * @return Response
     */
    public function eventsAction(Request $request, Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $eventSearchModel = new SearchEventModel($organisation, new \DateTime());

        $eventLineModels = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findEventLineModels($eventSearchModel);
        $arr["organisation"] = $organisation;
        return $this->renderWithBackUrl(
            'administration/organisation/events.html.twig',
            $arr + ["eventLineModels" => $eventLineModels],
            $this->generateUrl("administration_organisation_administer", ["organisation" => $organisation->getId()])
        );
    }
}