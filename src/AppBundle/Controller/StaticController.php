<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/12/2016
 * Time: 01:58
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class StaticController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        //get today's menus
        return $this->render(
            'static/index.html.twig'
        );
    }
}