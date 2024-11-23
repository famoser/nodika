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
    private int $issued = 0;

    private int $score = 0;

    private int $weight;

    private int $size;

    private int $id;

    public function __construct(int $id, int $weight, int $size)
    {
        $this->id = $id;
        $this->weight = $weight;
        $this->size = $size;
    }

    public function getIssued(): int
    {
        return $this->issued;
    }

    public function issue(): void
    {
        ++$this->issued;
        $this->score -= $this->size;
    }

    public function increment(): void
    {
        $this->score += $this->weight;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }
}
