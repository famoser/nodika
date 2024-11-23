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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[\Symfony\Component\Routing\Attribute\Route(path: '/email')]
class EmailController extends BaseController
{
    #[\Symfony\Component\Routing\Attribute\Route(path: '/{identifier}', name: 'email_view')]
    public function email($identifier): Response
    {
        $email = $this->getDoctrine()->getRepository('App:Email')->findOneBy(['identifier' => $identifier]);
        if (null === $email) {
            throw new NotFoundHttpException();
        }

        return $this->render('email/view.html.twig', ['myemail' => $email]);
    }
}
