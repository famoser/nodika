<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 08/04/2018
 * Time: 13:01
 */

namespace App\EventGeneration;


class QueueGenerator
{
    /**
     * @var QueueEntry[]
     */
    private $queueEntries;

    /**
     * IdealQueueHelper constructor.
     * @param array $queueMemberSize the members of the queue (memberId => relativeSize) (int => double)
     * @param array $initQueueMemberSize if you want to specify what happened before
     */
    public function __construct($queueMemberSize, $fixedStart = [])
    {
        $totalSum = 0;
        foreach ($queueMemberSize as $size) {
            $totalSum += $size;
        }

        //construct queue
        foreach ($queueMemberSize as $member => $size) {
            $queueEntry = new QueueEntry($member, $size, $totalSum);
            $this->queueEntries[$queueEntry->getPayload()] = $queueEntry;
        }

        //ensure the elements are unique
        $fixedStart = array_unique($fixedStart, SORT_NUMERIC);

        //force next
        foreach ($fixedStart as $item) {
            $this->forceNext($item);
        }
    }

    private function incrementAll()
    {
        foreach ($this->queueEntries as $queueEntry) {
            $queueEntry->increment();
        }
    }

    /**
     * @param int $member
     */
    public function forceNext($member)
    {
        $this->incrementAll();

        if (isset($this->queueEntries[$member]))
            $this->queueEntries[$member]->issue();
    }

    /**x
     * @return int
     */
    public function getNext()
    {
        $this->incrementAll();

        $minElement = null;
        $maxScore = 0;

        foreach ($this->queueEntries as $index => $entry) {
            if ($entry->getScore() > $maxScore) {
                $maxScore = $entry->getScore();
                $minElement = $entry;
            }
        }

        $minElement->issue();
        return $minElement->getPayload();
    }
}