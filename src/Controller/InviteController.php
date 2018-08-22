<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/02/2018
 * Time: 11:35
 */

namespace App\Controller;

use App\Controller\Base\BaseDoctrineController;
use App\Entity\Doctor;
use App\Form\Traits\User\ChangePasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/invite")
 */
class InviteController extends BaseDoctrineController
{
    /**
     * @Route("/{guid}", name="invite_index")
     *
     * @param $guid
     * @return Response
     */
    public function indexAction($guid)
    {
        $user = $this->getDoctrine()->getRepository(Doctor::class)->findBy(["invitationIdentifier" => $guid]);
        if ($user instanceof Doctor) {
            $form = $this->createForm(ChangePasswordType::class);
            $form->add("set_password", SubmitType::class);
            $arr["form"] = $form->createView();
            return $this->render('invite/index.html.twig', $arr);
        } else {
            return $this->render('invite/invalid.html.twig');
        }
    }
}
