<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/05/2017
 * Time: 14:50
 */

namespace AppBundle\Controller;


use AppBundle\Controller\Base\BaseController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organisation")
 */
class OrganisationController extends BaseController
{
    /**
     * @Route("/new", name="organisation_new")
     */
    public function newAction(Request $request)
    {

    }
}