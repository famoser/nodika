<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Helper;

use App\Entity\Base\BaseEntity;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineHelper
{
    public static function persistAndFlush(ManagerRegistry $registry, BaseEntity ...$entities): void
    {
        $manager = $registry->getManager();
        foreach ($entities as $entity) {
            $manager->persist($entity);
        }
        $manager->flush();
    }

    public static function removeAndFlush(ManagerRegistry $registry, BaseEntity ...$entities): void
    {
        $manager = $registry->getManager();
        foreach ($entities as $entity) {
            $manager->remove($entity);
        }
        $manager->flush();
    }
}
