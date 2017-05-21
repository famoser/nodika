<?php
use AppBundle\Entity\Traits\IdTrait;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 21/05/2017
 * Time: 14:36
 */

namespace AppBundle\Listener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

class DoctrinePreFlushListener implements EventSubscriber
{
    public function preFlush(PreFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $deletedEntity) {
            /* @var IdTrait $deletedEntity */
            $deletedEntity->setDeletedAt(new \DateTime('now'));
            $em->persist($deletedEntity);
        }
    }

    public function getSubscribedEvents()
    {
        return array(Events::preFlush);
    }
}