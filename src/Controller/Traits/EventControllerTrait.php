<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 4/1/18
 * Time: 11:23 AM
 */

namespace App\Controller\Traits;

use App\Entity\EventLine;
use App\Entity\FrontendUser;
use App\Helper\DateTimeFormatter;
use App\Model\EventLine\EventLineModel;
use Symfony\Component\Translation\TranslatorInterface;

trait EventControllerTrait
{
    /**
     * @param EventLineModel[] $eventModels
     * @param TranslatorInterface $translator
     *
     * @return string[][]
     */
    private function toDataTable($eventModels, TranslatorInterface $translator)
    {
        $data = [];
        foreach ($eventModels as $eventModel) {
            $row = [];
            $row[] = $eventModel->eventLine->getName();
            $row[] = $eventModel->eventLine->getDescription();
            $data[] = $row;
            $data[] = $this->getEventsHeader($translator);
            foreach ($eventModel->events as $event) {
                $row = [];
                $row[] = $event->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
                $row[] = $event->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
                $row[] = $event->getMember()->getName();
                if ($event->getFrontendUser() instanceof FrontendUser) {
                    $row[] = $event->getFrontendUser()->getFullName();
                }
                $data[] = $row;
            }
            $data[] = [];
        }

        return $data;
    }

    /**
     * @param TranslatorInterface $translator
     *
     * @return string[]
     */
    private function getEventsHeader(TranslatorInterface $translator)
    {
        $start = $translator->trans('start_date_time', [], 'entity_event');
        $end = $translator->trans('end_date_time', [], 'entity_event');
        $member = $translator->trans('entity.name', [], 'entity_member');
        $person = $translator->trans('entity.name', [], 'entity_frontend_user');

        return [$start, $end, $member, $person];
    }
}
