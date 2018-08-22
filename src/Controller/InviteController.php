<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/02/2018
 * Time: 11:35
 */

namespace App\Controller;

use App\Controller\Base\BaseLoginController;
use App\Entity\Doctor;
use App\Form\Traits\User\ChangePasswordType;
use App\Form\Traits\User\RequestInviteType;
use App\Model\Breadcrumb;
use App\Service\Interfaces\EmailServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
