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
     *
     * @return Response
     */
    public function indexAction($guid)
    {
        $user = $this->getDoctrine()->getRepository(Doctor::class)->findBy(['invitationIdentifier' => $guid]);
        if ($user instanceof Doctor) {
            $form = $this->createForm(ChangePasswordType::class);
            $form->add('set_password', SubmitType::class);
            $arr['form'] = $form->createView();

            return $this->render('invite/index.html.twig', $arr);
        }

        return $this->render('invite/invalid.html.twig');
    }
}
