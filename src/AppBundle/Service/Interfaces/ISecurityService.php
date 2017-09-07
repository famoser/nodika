<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/06/2017
 * Time: 16:28
 */

namespace AppBundle\Service\Interfaces;


interface ISecurityService
{
    /**
     * gets all emails of the administrators
     *
     * @return array
     */
    public function getAdminEmails();
}