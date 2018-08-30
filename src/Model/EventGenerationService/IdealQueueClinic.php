<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\EventGenerationService;

class IdealQueueClinic
{
    public $id;
    public $totalEventCount = 0;
    public $doneEventCount = 0;
    public $partDone = 0;

    public $totalWeekdayCount = 0;
    public $availableWeekdayCount = 0;

    public $totalSaturdayCount = 0;
    public $availableSaturdayCount = 0;

    public $totalSundayCount = 0;
    public $availableSundayCount = 0;

    public $totalHolidayCount = 0;
    public $availableHolidayCount = 0;

    private $history = [];

    public function assignWeekday($pointInTime)
    {
        --$this->availableWeekdayCount;
        $this->history[$pointInTime] = 0;
    }

    public function assignSaturday($pointInTime)
    {
        --$this->availableSaturdayCount;
        $this->history[$pointInTime] = 1;
    }

    public function assignSunday($pointInTime)
    {
        --$this->availableSundayCount;
        $this->history[$pointInTime] = 2;
    }

    public function assignHoliday($pointInTime)
    {
        --$this->availableHolidayCount;
        $this->history[$pointInTime] = 3;
    }

    /**
     * @param $pointInTime
     */
    public function removeAssignments($pointInTime)
    {
        $removeKeys = [];
        foreach (array_keys($this->history) as $array_key) {
            if ($array_key >= $pointInTime) {
                $removeKeys[] = $array_key;
            }
        }

        foreach ($removeKeys as $removeKey) {
            $val = (int) $this->history[$removeKey];
            if (0 === $val) {
                ++$this->availableWeekdayCount;
            } elseif (1 === $val) {
                ++$this->availableSaturdayCount;
            } elseif (2 === $val) {
                ++$this->availableSundayCount;
            } elseif (3 === $val) {
                ++$this->availableHolidayCount;
            }
            unset($this->history[$removeKey]);
        }
    }

    /**
     * calculates the part which is done.
     */
    public function calculatePartDone()
    {
        $this->partDone = (float) $this->doneEventCount / (float) $this->totalEventCount;
    }

    /**
     * resets the available properties.
     */
    public function setAllAvailable()
    {
        $this->availableWeekdayCount = $this->totalWeekdayCount;
        $this->availableSaturdayCount = $this->totalSaturdayCount;
        $this->availableSundayCount = $this->totalSundayCount;
        $this->availableHolidayCount = $this->totalHolidayCount;
    }

    /**
     * calculates total events by summing all event types.
     */
    public function calculateTotalEventCount()
    {
        $this->totalEventCount = 0;
        $this->totalEventCount += $this->totalWeekdayCount;
        $this->totalEventCount += $this->totalSaturdayCount;
        $this->totalEventCount += $this->totalSundayCount;
        $this->totalEventCount += $this->totalHolidayCount;
    }
}
