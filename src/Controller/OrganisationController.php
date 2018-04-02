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
use App\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organisation")
 * @Security("has_role('ROLE_USER')")
 */
class OrganisationController extends BaseController
{
    /**
     * @Route("/", name="organisation_view")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
    }
}
