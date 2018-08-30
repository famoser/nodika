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

use App\Controller\Base\BaseFormController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/assign")
 */
class AssignController extends BaseFormController
{
    /**
     * @Route("/", name="assign_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAction()
    {
        return $this->render('assign/index.html.twig');
    }
}
