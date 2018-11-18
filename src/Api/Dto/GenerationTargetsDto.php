<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Dto;

use App\Entity\Clinic;
use App\Entity\Doctor;

class GenerationTargetsDto
{
    /**
     * @var Doctor[]
     */
    private $doctors;

    /**
     * @var Clinic[]
     */
    private $clinics;

    /**
     * GenerationTargetsDto constructor.
     *
     * @param Doctor[] $doctors
     * @param Clinic[] $clinics
     */
    public function __construct(array $doctors, array $clinics)
    {
        $this->doctors = $doctors;
        $this->clinics = $clinics;
    }

    /**
     * @return Doctor[]
     */
    public function getDoctors(): array
    {
        return $this->doctors;
    }

    /**
     * @return Clinic[]
     */
    public function getClinics(): array
    {
        return $this->clinics;
    }
}
