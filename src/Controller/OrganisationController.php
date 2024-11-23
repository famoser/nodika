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
use App\Entity\Clinic;

#[\Symfony\Component\Routing\Attribute\Route(path: '/organisation')]
class OrganisationController extends BaseController
{
    #[\Symfony\Component\Routing\Attribute\Route(path: '/', name: 'organisation_index')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        $arr['clinics'] = $this->getDoctrine()->getRepository(Clinic::class)->findAll();

        return $this->render('organisation/index.html.twig', $arr);
    }
}
