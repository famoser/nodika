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
    #[ORM\Column(type: 'decimal')]
    private ?string $weight = 1;

    /**
     * @var float|null
     */
    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?string $generationScore = null;

    #[ORM\Column(type: 'integer')]
    private ?int $defaultOrder = 1;

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
