<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/02/2018
 * Time: 11:35
 */

namespace App\Controller;

use App\Controller\Base\BaseLoginController;
use App\Entity\FrontendUser;
use App\Form\Traits\User\SetPasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/invite")
 */
class InviteController extends BaseLoginController
{
    /**
     * @Route("/{guid}", name="invite_index")
     *
     * @param $guid
     * @return Response
     */
    public function indexAction($guid)
    {
        $user = $this->getDoctrine()->getRepository(FrontendUser::class)->findBy(["invitationIdentifier" => $guid]);
        if ($user instanceof FrontendUser) {
            $form = $this->createForm(SetPasswordType::class);
            $form->add("set_password", SubmitType::class);
            $arr["form"] = $form->createView();
            return $this->render('invite/index.html.twig', $arr);
        } else {
            return $this->render('invite/invalid.html.twig');
        }
    }

    /**
     * @Route("/request", name="invite_request")
     *
     * @return Response
     */
    public function requestAction()
    {
        return $this->render('invite/request.html.twig');
    }
}
