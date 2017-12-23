<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace App\Controller\Administration\Organisation\EventLine;

use App\Controller\Base\BaseController;
use App\Entity\EventLine;
use App\Entity\Organisation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/generate")
 * @Security("has_role('ROLE_USER')")
 */
class GenerateController extends BaseController
{
    /**
     * @Route("/choose", name="administration_organisation_event_line_generate_choose")
     * @param Request $request
     * @param Organisation $organisation
     * @param EventLine $eventLine
     * @return Response
     */
    public function chooseAction(Request $request, Organisation $organisation, EventLine $eventLine)
    {
        $arr["organisation"] = $organisation;
        $arr["eventLine"] = $eventLine;
        return $this->renderWithBackUrl(
            'administration/organisation/event_line/generate/choose.html.twig',
            $arr,
            $this->generateUrl("administration_organisation_event_line_administer", ["organisation" => $organisation->getId(), "eventLine" => $eventLine->getId()])
        );
    }
}
