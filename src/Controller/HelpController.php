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

use App\Controller\Base\BaseFrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/help")
 * @Security("has_role('ROLE_USER')")
 */
class HelpController extends BaseFrontendController
{
    /**
     * @Route("/users", name="help_users")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction()
    {
        return $this->renderWithBackUrl(
            'help/users.html.twig',
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
        return $this->renderWithBackUrl(
            'help/admins.html.twig',
            [],
            $this->generateUrl('dashboard_index')
        );
    }
}
