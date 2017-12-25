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

use App\Controller\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/help")
 * @Security("has_role('ROLE_USER')")
 */
class HelpController extends BaseController
{
    /**
     * @Route("/users", name="help_users")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction()
    {
        //todo
        return $this->renderWithBackUrl(
            'event/assign.html.twig',
            [],
            $this->generateUrl('dashboard_index')
        );
    }

    /**
     * @Route("/admins", name="help_admins")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminsAction()
    {
        //todo
        return $this->renderWithBackUrl(
            'event/assign.html.twig',
            [],
            $this->generateUrl('dashboard_index')
        );
    }
}
