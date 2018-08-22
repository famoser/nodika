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

trait SoftDeleteTrait
{
    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt = null;

    /**
     * returns if the person can be invited
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deletedAt != null;
    }

    /**
     * soft deletes
     */
    public function delete()
    {
        $this->deletedAt = new \DateTime();
    }

    /**
     * @return \DateTime|null
     */
    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }
}
