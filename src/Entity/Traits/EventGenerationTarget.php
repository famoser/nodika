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

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }

    public function getGenerationScore(): ?float
    {
        return $this->generationScore;
    }

    public function setGenerationScore(?float $generationScore): void
    {
        $this->generationScore = $generationScore;
    }

    public function getDefaultOrder(): int
    {
        return $this->defaultOrder;
    }

    public function setDefaultOrder(int $defaultOrder): void
    {
        $this->defaultOrder = $defaultOrder;
    }
}
