<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 21/06/2017
 * Time: 15:25
 */

namespace AppBundle\Extension;

use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Service\Interfaces\ISessionService;
use Twig_Extension;

class MyTwigExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('myDate', array($this, 'dateFilter')),
            new \Twig_SimpleFilter('myDateTime', array($this, 'dateTimeFilter'))
        );
    }

    /**
     * @param \DateTime $date
     * @return string
     */
    public function dateFilter($date)
    {
        return $date->format("d.m.Y");
    }

    /**
     * @param \DateTime $date
     * @return string
     */
    public function dateTimeFilter($date)
    {
        return $date->format("d.m.Y H:i");
    }
}