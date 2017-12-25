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
use phpDocumentor\Reflection\Types\Boolean;
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
        ];
    }

    private function prependDayName(DateTime $date)
    {
        return $this->translator->trans('date_time.'.$date->format('D'), [], 'framework');
    }

    /**
     * @param \DateTime $date
     *
     * @return string
     */
    public function dateFilter($date)
    {
        if ($date instanceof \DateTime) {
            return $this->prependDayName($date).', '.$date->format(DateTimeFormatter::DATE_FORMAT);
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
            return $this->prependDayName($date).', '.$date->format(DateTimeFormatter::DATE_TIME_FORMAT);
        }

        return '-';
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
