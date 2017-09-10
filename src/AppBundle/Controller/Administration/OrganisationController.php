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
use AppBundle\Enum\ApplicationEventType;
use AppBundle\Enum\SubmitButtonType;
use AppBundle\Form\Organisation\OrganisationType;
use AppBundle\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        return $this->render(
            'administration/organisation/new.html.twig', $arr
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
                $translator->trans("messages.not_fully_setup", [], "organisation"),
                $this->generateUrl("administration_organisation_setup", ["organisation" => $organisation->getId()])
            );
        }

        $arr["organisation"] = $organisation;
        $setupStatus = $this->getDoctrine()->getRepository("AppBundle:Organisation")->getSetupStatus($organisation);
        return $this->render(
            'administration/organisation/administer.html.twig', $arr + ["organisation" => $organisation, "setupStatus" => $setupStatus]
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
        return $this->render(
            'administration/organisation/setup.html.twig', $arr + ["setupStatus" => $setupStatus]
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
        return $this->render(
            'administration/organisation/members.html.twig',
            $arr
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

        /* @var Member[] $notInvitedMembers */
        $notInvitedMembers = [];
        foreach ($organisation->getMembers() as $member) {
            if (!$member->getHasBeenInvited()) {
                $notInvitedMembers[] = $member;
            }
        }

        $arr["organisation"] = $organisation;
        return $this->render(
            'administration/organisation/members_invite.html.twig',
            $arr + ["notInvitedMembers" => $notInvitedMembers]
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
        return $this->render(
            'administration/organisation/event_lines.html.twig',
            $arr
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

        $arr["organisation"] = $organisation;
        return $this->render(
            'administration/organisation/settings.html.twig',
            $arr
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

        $eventLines = $this->getDoctrine()->getRepository("AppBundle:Organisation")->findEventLineModels($organisation, new \DateTime(), ">");
        $arr["organisation"] = $organisation;
        return $this->render(
            'administration/organisation/events.html.twig',
            $arr + ["eventLineModels" => $eventLines]
        );
    }
}