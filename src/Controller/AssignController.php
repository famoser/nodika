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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/assign')]
class AssignController extends BaseFormController
{
    #[Route(path: '/', name: 'assign_index')]
    public function assign(): Response
    {
        return $this->render('assign/index.html.twig');
    }
}
