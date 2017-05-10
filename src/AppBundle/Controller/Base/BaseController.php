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
     * @param string $message the translation message to display
     * @param string $domain the domain of the error message
     */
    protected function displayError($message, $domain)
    {
        $this->get('session')->getFlashBag()->set('error', $this->get("translator")->trans($message, [], $domain));
    }

    /**
     * displays the default form error
     */
    protected function displayFormValidationError()
    {
        $this->displayError("error.form_validation_failed", "common");
    }
}