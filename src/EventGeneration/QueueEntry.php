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
    private $id;

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

    /**
     * @return int
     */
    public function getId()
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
