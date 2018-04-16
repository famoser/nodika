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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/account")
 * @Security("has_role('ROLE_USER')")
 */
class AccountController extends BaseDoctrineController
{
    /**
     * @Route("/", name="account_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $arr["user"] = $this->getUser();
        return $this->render('account/index.html.twig', $arr);
    }
}
