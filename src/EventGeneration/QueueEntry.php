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

class QueueEntry
{
    /**
     * @var int
     */
    private $issued = 0;

    /**
     * @var int
     */
    private $score = 0;

    /**
     * @var int
     */
    private $weight = 0;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $payload;

    public function __construct($payload, $weight, $size)
    {
        $this->payload = $payload;
        $this->weight = $weight;
        $this->size = $size;
    }

    public function getIssued(): int
    {
        return $this->issued;
    }

    /**
     * issues the queue entry.
     */
    public function issue(): void
    {
        ++$this->issued;
        $this->score -= $this->size;
    }

    /**
     * issues the queue entry.
     */
    public function increment(): void
    {
        $this->score += $this->weight;
    }

    /**
     * @return int
     */
    public function getPayload()
    {
        return $this->payload;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    /**
     * @return QueueEntry
     */
    public function exactClone()
    {
        $entry = new self($this->payload, $this->weight, $this->size);
        $entry->issued = $this->issued;
        $entry->score = $this->score;

        return $entry;
    }
}
