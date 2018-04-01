<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration\Organisation\EventLine;

use App\Controller\Base\BaseController;
use App\Entity\EventLine;
use App\Entity\Organisation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/generate")
 * @Security("has_role('ROLE_USER')")
 */
class GenerateController extends BaseController
{
    /**
     * @Route("/", name="administration_generate_index")
     *
     * @param Organisation $organisation
     * @param EventLine $eventLine
     *
     * @return Response
     */
    public function chooseAction(Organisation $organisation, EventLine $eventLine)
    {
        $arr['organisation'] = $organisation;
        $arr['eventLine'] = $eventLine;

        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/choose.html.twig',
            $arr,
            $this->generateUrl('administration_organisation_event_line_administer', ['organisation' => $organisation->getId(), 'eventLine' => $eventLine->getId()])
        );
    }
}
