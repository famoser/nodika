<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseFrontendController;
use App\Controller\Traits\EventControllerTrait;
use App\Entity\Organisation;
use App\Enum\ApplicationEventType;
use App\Enum\SubmitButtonType;
use App\Model\Event\SearchEventModel;
use App\Security\Voter\OrganisationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/administration")
 * @Security("has_role('ROLE_USER')")
 */
class AdministrationController extends BaseFrontendController
{
    use EventControllerTrait;

    /**
     * @Route("/", name="administration_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $organisation = $this->getOrganisation();
        $searchModel = new SearchEventModel($organisation, new \DateTime());
        $searchModel->setEndDateTime(new \DateTime('today + 1 month'));

        $organisationRepo = $this->getDoctrine()->getRepository('App:Organisation');

        $eventLineModels = $organisationRepo->findEventLineModels($searchModel);
        $arr['has_active_events'] = $organisationRepo->addActiveEvents($eventLineModels);
        $arr['event_line_models'] = $eventLineModels;


        $setupStatus = $this->getDoctrine()->getRepository('App:Organisation')->getSetupStatus($organisation);
        $arr["setup_finished"] = $setupStatus->getAllDone();

        return $this->renderNoBackUrl('administration/index.html.twig', $arr, 'dashboard!');
    }

    /**
     * @Route("/events", name="administration_events")
     *
     * @return Response
     */
    public function eventsAction()
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
     * @Route("/persons", name="administration_persons")
     *
     * @return Response
     */
    public function personsAction()
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
     * @Route("/members", name="administration_organisation_members")
     *
     * @return Response
     */
    public function membersAction()
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
     * @Route("/event_lines", name="administration_organisation_event_lines")
     *
     * @return Response
     */
    public function eventLinesAction()
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
     * @Route("/setup", name="administration_organisation_setup")
     *
     * @return Response
     */
    public function setupAction()
    {
        $organisation = $this->getOrganisation();
        $setupStatus = $this->getDoctrine()->getRepository('App:Organisation')->getSetupStatus($organisation);

        $arr['organisation'] = $organisation;

        return $this->renderWithBackUrl(
            'administration/organisation/setup.html.twig',
            $arr + ['setupStatus' => $setupStatus],
            $this->generateUrl('administration_organisation_administer', ['organisation' => $organisation->getId()])
        );
    }

    /**
     * @Route("/settings", name="administration_organisation_settings")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param TranslatorInterface $translator
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function settingsAction(Request $request, Organisation $organisation, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);
        $this->getDoctrine()->getRepository('App:ApplicationEvent')->registerEventOccurred($organisation, ApplicationEventType::VISITED_SETTINGS);
        $organisationSetting = $this->getDoctrine()->getRepository('App:OrganisationSetting')->getByOrganisation($organisation);

        $form = $this->handleCrudForm(
            $request,
            $translator,
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
}
