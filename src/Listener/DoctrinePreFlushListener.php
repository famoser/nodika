<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Listener;

use App\Entity\Traits\IdTrait;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\ORMException;

class DoctrinePreFlushListener implements EventSubscriber
{
    public function preFlush(PreFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $deletedEntity) {
            /* @var IdTrait $deletedEntity */
            $deletedEntity->setDeletedAt(new \DateTime('now'));
            try {
                $em->persist($deletedEntity);
            } catch (\Exception $e) {
                //we can't do anything if the database fails
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::preFlush];
    }
}
