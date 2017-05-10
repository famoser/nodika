<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/12/2016
 * Time: 01:50
 */

namespace AppBundle\Controller\Base;

use AppBundle\Entity\FrontendUser;
use AppBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /**
     * @param string $message the translation message to display
     */
    protected function displayError($message)
    {
        $this->get('session')->getFlashBag()->set('error', $message);
    }

    /**
     * displays the default form error
     */
    protected function displayFormValidationError()
    {
        $this->displayError($this->get("translator")->trans("error.form_validation_failed", [], "common"));
    }

    /**
     * @return Person
     */
    protected function getPerson()
    {
        $user = $this->getUser();
        return $user->getPerson();
    }

    /**
     * @return FrontendUser
     */
    protected function getUser()
    {
        return parent::getUser();
    }
}