<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait EventGenerationTarget
{
    /**
     * @var float
     *
     * @ORM\Column(type="decimal")
     */
    private $weight = 1;

    /**
     * @var float|null
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $generationScore;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $defaultOrder = 1;

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
    public function getDefaultOrder(): int
    {
        return $this->defaultOrder;
    }

    /**
     * @param int $defaultOrder
     */
    public function setDefaultOrder(int $defaultOrder): void
    {
        $this->defaultOrder = $defaultOrder;
    }
}
