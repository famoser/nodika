<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/12/2016
 * Time: 01:50
 */

namespace AppBundle\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /**
     * @param $message
     */
    protected function displayError($message, $domain)
    {

        $this->get('session')->getFlashBag()->set('error', $this->get("translator")->trans($message, [], $domain));
    }

    /**
     *
     */
    protected function displayFormValidationError()
    {
        $this->displayError($this->get("translator")->trans("error.form_validation_failed", [], "common"));
    }
}