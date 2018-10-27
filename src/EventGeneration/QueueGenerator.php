<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @param array $clinicRelativeSize the clinics of the queue (clinicId => relativeSize) (int => double)
     */
    public function __construct($clinicRelativeSize)
    {
        $totalSum = 0;
        foreach ($clinicRelativeSize as $size) {
            $totalSum += $size;
        }
        $this->totalScore = $totalSum;

        //construct queue
        foreach ($clinicRelativeSize as $clinic => $size) {
            $queueEntry = new QueueEntry($clinic, $size, $totalSum);
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
        if (0 !== $totalScore) {
            $diff = $this->totalScore / (float) $totalScore;
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
     * @param int $clinic
     */
    public function forceNext($clinic)
    {
        $this->incrementAll();

        if (isset($this->queueEntries[$clinic])) {
            $this->queueEntries[$clinic]->issue();
        }
    }

    /**
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

    /** @var QueueEntry[] */
    private $queueEntriesBackup = [];

    /**
     * preserves the state.
     */
    public function snapshot()
    {
        $this->queueEntriesBackup = [];
        foreach ($this->queueEntries as $key => $value) {
            $this->queueEntriesBackup[$key] = clone $value;
        }
    }

    /**
     * resets the state to when the last time recoverSnapshot was called.
     */
    public function recoverSnapshot()
    {
        $this->queueEntries = $this->queueEntriesBackup;
    }
}
