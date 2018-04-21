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

class TwigExtension extends Twig_Extension
{
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * makes the filters available to twig
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('dateFormat', [$this, 'dateFormatFilter']),
            new Twig_SimpleFilter('dateTimeFormat', [$this, 'dateTimeFilter']),
            new Twig_SimpleFilter('booleanFormat', [$this, 'booleanFilter']),
            new Twig_SimpleFilter('camelCaseToUnderscore', [$this, 'camelCaseToUnderscoreFilter']),

        ];
    }

    /**
     * @param string $propertyName
     *
     * @return string
     */
    public function camelCaseToUnderscoreFilter($propertyName)
    {
        return mb_strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $propertyName));
    }

    /**
     * @param \DateTime $date
     *
     * @return string
     */
    public function dateFormatFilter($date)
    {
        if ($date instanceof \DateTime) {
            return $this->prependDayName($date) . ', ' . $date->format(DateTimeFormatter::DATE_FORMAT);
        }

        return '-';
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
     * translates the day of the week
     * @param DateTime $date
     * @return string
     */
    private function prependDayName(DateTime $date)
    {
        return $this->translator->trans('date_time.' . $date->format('D'), [], 'framework');
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function booleanFilter($value)
    {
        if ($value) {
            return BooleanType::getTranslation(BooleanType::YES, $this->translator);
        }

        return BooleanType::getTranslation(BooleanType::NO, $this->translator);
    }
}
