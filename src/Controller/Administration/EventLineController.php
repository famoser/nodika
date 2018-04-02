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
use App\Entity\Event;
use App\Entity\EventLine;
use App\Entity\Member;
use App\Entity\Organisation;
use App\Enum\SubmitButtonType;
use App\Form\Event\ImportEventsType;
use App\Helper\DateTimeFormatter;
use App\Helper\StaticMessageHelper;
use App\Model\Form\ImportFileModel;
use App\Security\Voter\EventLineVoter;
use App\Security\Voter\OrganisationVoter;
use App\Service\ExchangeService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/event_line")
 * @Security("has_role('ROLE_USER')")
 */
class EventLineController extends BaseFormController
{
    /**
     * @Route("/new", name="administration_event_line_new")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $myForm = $this->handleCreateForm($request, new EventLine());

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['new_form'] = $myForm->createView();

        return $this->render('administration/event_line/new.html.twig');
    }

    /**
     * @Route("/{eventLine}/edit", name="administration_event_line_edit")
     *
     * @param Request $request
     * @param EventLine $eventLine
     *
     * @return Response
     */
    public function editAction(Request $request, EventLine $eventLine)
    {
        $myForm = $this->handleUpdateForm($request, $eventLine);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['edit_form'] = $myForm->createView();

        return $this->render('administration/event_line/edit.html.twig');
    }

    /**
     * @Route("/{eventLine}/remove", name="administration_event_line_remove")
     *
     * @param Request $request
     * @param EventLine $eventLine
     *
     * @return Response
     */
    public function removeAction(Request $request, EventLine $eventLine)
    {
        $myForm = $this->handleRemoveForm($request, $eventLine);

        if ($myForm instanceof Response) {
            return $myForm;
        }

        $arr['remove_form'] = $myForm->createView();

        return $this->render('administration/event_line/remove.html.twig');
    }
}
