<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/05/2017
 * Time: 14:22
 */

namespace AppBundle\Controller\Administration;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Organisation;
use AppBundle\Form\Organisation\NewOrganisationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/person")
 * @Security("has_role('ROLE_USER')")
 */
class PersonController extends BaseController
{
    /**
     * @Route("/{person}/view", name="admin_person_view")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function viewAction(Request $request)
    {
        $newOrganisationForm = $this->createForm(NewOrganisationType::class);
        $arr = [];

        $organisation = Organisation::createFromPerson($this->getPerson());
        $newOrganisationForm->setData($organisation);
        $newOrganisationForm->handleRequest($request);

        if ($newOrganisationForm->isSubmitted()) {
            if ($newOrganisationForm->isValid()) {
                $organisation->setActiveEnd(new \DateTime("today + 31 days"));
                $organisation->setIsActive(true);
                $organisation->addLeader($this->getPerson());
                $em = $this->getDoctrine()->getManager();
                $em->persist($organisation);
                $em->flush();

                return $this->redirectToRoute("organisation_setup", ["organisation" => $organisation->getId()]);
            } else {
                $this->displayFormValidationError();
            }
        }

        $arr["new_organisation_form"] = $newOrganisationForm->createView();
        return $this->render(
            'organisation/new.html.twig', $arr
        );
    }
}