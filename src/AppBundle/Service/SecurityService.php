<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 08/07/2017
 * Time: 14:00
 */

namespace AppBundle\Service;


use AppBundle\Service\Interfaces\ISecurityService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SecurityService implements ISecurityService
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * SecurityService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * gets all emails of the administrators
     *
     * @return array
     */
    public function getAdminEmails()
    {
        return [
            $this->container->getParameter("client_email"),
            $this->container->getParameter("company_email"),
            $this->container->getParameter("developer_email")
        ];
    }
}