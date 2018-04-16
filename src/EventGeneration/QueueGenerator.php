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
     * @var int|mixed
     */
    private $totalScore;

    /**
     * IdealQueueHelper constructor.
     * @param array $memberRelativeSize the members of the queue (memberId => relativeSize) (int => double)
     */
    public function __construct($memberRelativeSize)
    {
        $totalSum = 0;
        foreach ($memberRelativeSize as $size) {
            $totalSum += $size;
        }
        $this->totalScore = $totalSum;

        //construct queue
        foreach ($memberRelativeSize as $member => $size) {
            $queueEntry = new QueueEntry($member, $size, $totalSum);
            $this->queueEntries[$queueEntry->getPayload()] = $queueEntry;
        }
    }

    /**
     * @param array $entries if you want to specify what happened before
     */
    public function warmUp($entries)
    {
        //preserve original scores
        $originalScores = [];
        foreach ($this->queueEntries as $id => $queueEntry) {
            $originalScores[$id] = $queueEntry->getScore();
        }

        //simulate queue for $entries
        $accessed = [];
        foreach ($entries as $entry) {
            if (isset($this->queueEntries[$entry])) {
                $this->incrementAll();
                $this->queueEntries[$entry]->issue();
                $accessed[$entry] = true;
            }
        }

        //reset scores of those which did not occur in $entries
        $totalScore = 0;
        foreach ($this->queueEntries as $id => $entry) {
            if (!isset($accessed[$id])) {
                $entry->setScore($originalScores[$id]);
            }
            $totalScore += $entry->getScore();
        }

        //normalize scores
        if ($totalScore != 0) {
            $diff = $this->totalScore / (double)$totalScore;
            foreach ($this->queueEntries as $queueEntry) {
                $queueEntry->setScore($queueEntry->getScore() * $diff);
            }
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

        if (isset($this->queueEntries[$member])) {
            $this->queueEntries[$member]->issue();
        }
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
