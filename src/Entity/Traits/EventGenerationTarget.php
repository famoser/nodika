<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 4/5/18
 * Time: 2:47 PM
 */

namespace App\Entity\Traits;


use App\Entity\EventGeneration;

trait EventGenerationTarget
{
    /**
     * @var double
     *
     * @ORM\Column(type="decimal")
     */
    private $weight = 1;

    /**
     * @var double|null
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $generationScore;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $order = 1;

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     */
    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return float|null
     */
    public function getGenerationScore(): ?float
    {
        return $this->generationScore;
    }

    /**
     * @param float|null $generationScore
     */
    public function setGenerationScore(?float $generationScore): void
    {
        $this->generationScore = $generationScore;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }
}