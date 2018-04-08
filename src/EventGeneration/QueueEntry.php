<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 08/04/2018
 * Time: 13:03
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
    private $issuedWeight = 0;

    /**
     * @var int $score
     */
    private $score = 0;

    /**
     * @var int
     */
    private $incrementWeight;

    /**
     * @var int
     */
    private $payload;

    public function __construct($payload, $issueWeight, $incrementWeight = 1)
    {
        $this->payload = $payload;
        $this->issuedWeight = $issueWeight;
        $this->incrementWeight = $incrementWeight;
    }

    /**
     * @return int
     */
    public function getIssued(): int
    {
        return $this->issued;
    }

    /**
     * issues the queue entry
     */
    public function issue(): void
    {
        $this->issued++;
        $this->score -= $this->issuedWeight;
    }

    /**
     * issues the queue entry
     */
    public function increment(): void
    {
        $this->issued++;
        $this->score += $this->incrementWeight;
    }

    /**
     * @return int
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }
}