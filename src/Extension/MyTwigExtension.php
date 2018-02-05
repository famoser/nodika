<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extension;

use App\Enum\BooleanType;
use App\Helper\DateTimeFormatter;
use DateTime;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Extension;
use Twig_SimpleFilter;

class MyTwigExtension extends Twig_Extension
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('myDate', [$this, 'dateFilter']),
            new Twig_SimpleFilter('myDateTime', [$this, 'dateTimeFilter']),
            new Twig_SimpleFilter('myBoolean', [$this, 'booleanFilter']),
            new Twig_SimpleFilter('themeColorToHex', [$this, 'themeColorToHexFilter'])
        ];
    }

    /**
     * @param \DateTime $date
     *
     * @return string
     */
    public function dateFilter($date)
    {
        if ($date instanceof \DateTime) {
            return $this->prependDayName($date) . ', ' . $date->format(DateTimeFormatter::DATE_FORMAT);
        }

        return '-';
    }

    private function prependDayName(DateTime $date)
    {
        return $this->translator->trans('date_time.' . $date->format('D'), [], 'framework');
    }

    /**
     * @param \DateTime $date
     *
     * @return string
     */
    public function dateTimeFilter($date)
    {
        if ($date instanceof \DateTime) {
            return $this->prependDayName($date) . ', ' . $date->format(DateTimeFormatter::DATE_TIME_FORMAT);
        }

        return '-';
    }

    /**
     * @param string $color
     *
     * @return string
     */
    public function themeColorToHexFilter($color)
    {
        $colorArray = [
            "blue" => "555F76",
            "brown" => "B19E7A",
            "green" => "618D61",
            "green2" => "496A6A",
            "red" => "B17A7A"
        ];
        return "#" . $colorArray[$color];
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function booleanFilter($value)
    {
        if ($value) {
            return $this->translator->trans(BooleanType::getTranslation(BooleanType::YES), [], 'enum_boolean_type');
        }

        return $this->translator->trans(BooleanType::getTranslation(BooleanType::NO), [], 'enum_boolean_type');
    }
}
