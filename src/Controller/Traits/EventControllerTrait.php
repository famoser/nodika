<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Traits;

use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\Event;
use App\Helper\DateTimeFormatter;
use Symfony\Component\Translation\TranslatorInterface;

trait EventControllerTrait
{
    /**
     * @param Event[]             $events
     * @param TranslatorInterface $translator
     *
     * @return string[][]
     */
    private function toDataTable($events, TranslatorInterface $translator)
    {
        $data = [];
        $data[] = $this->getEventsHeader($translator);
        foreach ($events as $event) {
            $row = [];
            $row[] = $event->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
            $row[] = $event->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
            $row[] = $event->getClinic() instanceof Clinic ? $event->getClinic()->getName() : '';
            $row[] = $event->getDoctor() instanceof Doctor ? $event->getDoctor()->getFullName() : '';
            $tags = [];
            foreach ($event->getEventTags() as $eventTag) {
                $tags[] = $eventTag->getName();
            }
            $row[] = implode(',', $tags);
            $data[] = $row;
        }
        $data[] = [];

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
        $clinic = $translator->trans('entity.name', [], 'entity_clinic');
        $person = $translator->trans('entity.plural', [], 'entity_event_tag');

        return [$start, $end, $clinic, $person];
    }
}
