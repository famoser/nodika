<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 21/06/2017
 * Time: 15:25
 */

namespace AppBundle\Extension;

use AppBundle\Helper\DateTimeFormatter;
use AppBundle\Service\Interfaces\ISessionService;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Extension;

class MyTwigExtension extends Twig_Extension
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('myDate', array($this, 'dateFilter')),
            new \Twig_SimpleFilter('myDateTime', array($this, 'dateTimeFilter')),
            new \Twig_SimpleFilter('myBoolean', array($this, 'booleanFilter'))
        );
    }

    /**
     * @param \DateTime $date
     * @return string
     */
    public function dateFilter($date)
    {
        return $date->format(DateTimeFormatter::DATE_FORMAT);
    }

    /**
     * @param \DateTime $date
     * @return string
     */
    public function dateTimeFilter($date)
    {
        return $date->format(DateTimeFormatter::DATE_TIME_FORMAT);
    }

    /**
     * @param $value
     * @return string
     */
    public function booleanFilter($value)
    {
        if ($value) {
            return $this->translator->trans("true", [], "enum_boolean_type");
        } else {
            return $this->translator->trans("false", [], "enum_boolean_type");
        }
    }
}