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
use App\Controller\Base\BaseFormController;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Enum\SubmitButtonType;
use App\Form\Member\ImportMembersType;
use App\Form\Member\MemberType;
use App\Helper\HashHelper;
use App\Model\Form\ImportFileModel;
use App\Security\Voter\MemberVoter;
use App\Security\Voter\OrganisationVoter;
use App\Service\CsvService;
use App\Service\EmailService;
use App\Service\ExchangeService;
use App\Service\Interfaces\CsvServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/members")
 * @Security("has_role('ROLE_USER')")
 */
class MemberController extends BaseFormController
{
    /**
     * @Route("/new", name="administration_member_new")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $myForm = $this->handleCreateForm($request, new Member());

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['new_form'] = $myForm->createView();

        return $this->render('administration/member/new.html.twig');
    }

    /**
     * @Route("/{member}/edit", name="administration_member_edit")
     *
     * @param Request $request
     * @param Member $member
     *
     * @return Response
     */
    public function editAction(Request $request, Member $member)
    {
        $myForm = $this->handleUpdateForm($request, $member);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['edit_form'] = $myForm->createView();

        return $this->render('administration/member/edit.html.twig');
    }

    /**
     * @Route("/{member}/remove", name="administration_member_remove")
     *
     * @param Request $request
     * @param Member $member
     *
     * @return Response
     */
    public function removeAction(Request $request, Member $member)
    {
        $myForm = $this->handleRemoveForm($request, $member);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['remove_form'] = $myForm->createView();

        return $this->render('administration/member/remove.html.twig');
    }
}
