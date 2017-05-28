<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2017
 * Time: 16:10
 */

namespace AppBundle\Helper;


use AppBundle\Entity\Event;
use AppBundle\Entity\EventPast;
use AppBundle\Entity\Person;
use AppBundle\Enum\EventChangeType;
use AppBundle\Model\EventLine\ChangeModel\Base\EventLineBaseChangeModel;
use AppBundle\Model\EventLine\ChangeModel\ChangedByAdminChange;
use AppBundle\Model\EventLine\ChangeModel\CreatedByAdminChange;
use AppBundle\Model\EventLine\ChangeModel\RemovedByAdminChange;

class EventLineChangeHelper
{
    /**
     * @param Event $event
     * @param Person $admin
     * @return EventPast
     */
    public static function createCreatedByAdminChange(Event $event, Person $admin)
    {
        $changeModel = new CreatedByAdminChange();
        $changeModel->adminIdentifier = $admin->getFullName();
        return static::createFromEvent($event, $changeModel, EventChangeType::CREATED_BY_ADMIN);
    }

    /**
     * @param Event $event
     * @param Event $oldEvent
     * @param Person $admin
     * @return EventPast
     */
    public static function createChangedByAdminChange(Event $event, Event $oldEvent, Person $admin)
    {
        $changeModel = new ChangedByAdminChange();
        $changeModel->adminIdentifier = $admin->getFullName();
        if ($event->getStartDateTime() != $oldEvent->getStartDateTime()) {
            $changeModel->changedStartDateTime = true;
            $changeModel->oldStartDateTime = $oldEvent->getStartDateTime();
            $changeModel->newStartDateTime = $event->getStartDateTime();
        }
        if ($event->getEndDateTime() != $oldEvent->getEndDateTime()) {
            $changeModel->changedEndDateTime = true;
            $changeModel->oldEndDateTime = $oldEvent->getEndDateTime();
            $changeModel->newEndDateTime = $event->getEndDateTime();
        }
        if ($event->getMember()->getFullIdentifier() != $oldEvent->getMember()->getFullIdentifier()) {
            $changeModel->changedMember = true;
            $changeModel->oldMemberName = $oldEvent->getMember()->getFullIdentifier();
            $changeModel->newMemberName = $event->getMember()->getFullIdentifier();
            $event->setPerson(null);
        }
        return static::createFromEvent($event, $changeModel, EventChangeType::CHANGED_BY_ADMIN);
    }
    /**
     * @param Event $event
     * @param Person $admin
     * @return EventPast
     */
    public static function createRemovedByAdminChange(Event $event, Person $admin)
    {
        $changeModel = new RemovedByAdminChange();
        $changeModel->adminIdentifier = $admin->getFullName();
        return static::createFromEvent($event, $changeModel, EventChangeType::REMOVED_BY_ADMIN);
    }

    /**
     * @param Event $event
     * @param EventLineBaseChangeModel $changeModel
     * @param $changeType
     * @return EventPast
     */
    private static function createFromEvent(Event $event, EventLineBaseChangeModel $changeModel, $changeType)
    {
        $eventPast = new EventPast();
        $eventPast->setEvent($event);
        $eventPast->setEventJson($event->createJson());
        $eventPast->setChangeDateTime(new \DateTime());
        $eventPast->setChangeType($changeType);
        $eventPast->setChangeConfigurationJson(json_encode($changeModel));
        return $eventPast;
    }
}