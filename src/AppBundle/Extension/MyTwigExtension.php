<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 21/06/2017
 * Time: 15:25
 */

namespace AppBundle\Extension;

use AppBundle\Helper\DateTimeFormatter;
use DateTime;
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

    private function prependDayName(DateTime $date)
    {
        return $this->translator->trans("date_time." . $date->format("D"), [], "framework");
    }

    /**
     * @param \DateTime $date
     * @return string
     */
    public function dateFilter($date)
    {
        if ($date instanceof \DateTime) {

            return $this->prependDayName($date) . ", " . $date->format(DateTimeFormatter::DATE_FORMAT);
        }
        return "-";
    }

    /**
     * @param \DateTime $date
     * @return string
     */
    public function dateTimeFilter($date)
    {
        if ($date instanceof \DateTime) {
            return $this->prependDayName($date) . ", " . $date->format(DateTimeFormatter::DATE_TIME_FORMAT);
        }
        return "-";
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