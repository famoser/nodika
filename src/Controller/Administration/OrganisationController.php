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
use App\Controller\Base\BaseFrontendController;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Entity\Person;
use App\Enum\ApplicationEventType;
use App\Enum\SubmitButtonType;
use App\Form\Organisation\OrganisationType;
use App\Helper\HashHelper;
use App\Model\Event\SearchEventModel;
use App\Security\Voter\OrganisationVoter;
use App\Service\EmailService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/organisation")
 * @Security("has_role('ROLE_USER')")
 */
class OrganisationController extends BaseFrontendController
{
    /**
     * @Route("/edit", name="administration_organisation_edit")
     *
     * @param Request $request
     * @param Organisation $organisation
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function editAction(Request $request, Organisation $organisation, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADMINISTRATE, $organisation);

        $myForm = $this->handleCrudForm(
            $request,
            $translator,
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
}
