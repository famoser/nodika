<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14/09/2017
 * Time: 08:39
 */

namespace App\Model\EventGenerationService;

class IdealQueueMember
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
        $this->availableWeekdayCount--;
        $this->history[$pointInTime] = 0;
    }

    public function assignSaturday($pointInTime)
    {
        $this->availableSaturdayCount--;
        $this->history[$pointInTime] = 1;
    }

    public function assignSunday($pointInTime)
    {
        $this->availableSundayCount--;
        $this->history[$pointInTime] = 2;
    }

    public function assignHoliday($pointInTime)
    {
        $this->availableHolidayCount--;
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
            $val = $this->history[$removeKey];
            if ($val == 0) {
                $this->availableWeekdayCount++;
            } elseif ($val == 1) {
                $this->availableSaturdayCount++;
            } elseif ($val == 2) {
                $this->availableSundayCount++;
            } elseif ($val == 3) {
                $this->availableHolidayCount++;
            }
            unset($this->history[$removeKey]);
        }
    }

    /**
     * calculates the part which is done
     */
    public function calculatePartDone()
    {
        $this->partDone = (double)$this->doneEventCount / (double)$this->totalEventCount;
    }

    /**
     * resets the available properties
     */
    public function setAllAvailable()
    {
        $this->availableWeekdayCount = $this->totalWeekdayCount;
        $this->availableSaturdayCount = $this->totalSaturdayCount;
        $this->availableSundayCount = $this->totalSundayCount;
        $this->availableHolidayCount = $this->totalHolidayCount;
    }

    /**
     * calculates total events by summing all event types
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
