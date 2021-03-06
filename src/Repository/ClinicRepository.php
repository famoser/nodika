<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ClinicRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClinicRepository extends EntityRepository
{
    /**
     * adds a default ordering.
     *
     * @param null $limit
     * @param null $offset
     *
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $orderBy = null === $orderBy ? ['name' => 'ASC'] : $orderBy;

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }
}
