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
use App\Service\CsvService;
use Symfony\Contracts\Translation\TranslatorInterface;

trait EventControllerTrait
{
    /**
     * @param Event[] $events
     *
     * @return string[][]
     */
    private function toDataTable($events)
    {
        $data = [];
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
            $row[] = implode(CsvService::DELIMITER, $tags);
            $data[] = $row;
        }
        $data[] = [];

        return $data;
    }

    /**
     * @param Event[] $events
     *
     * @return string[][]
     */
    private function toSummaryTable($events)
    {
        if (0 === \count($events)) {
            return [];
        }

        $data = [];

        // set start / end datetime
        $startDateTime = $events[0]->getStartDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
        $endDateTime = $events[\count($events) - 1]->getEndDateTime()->format(DateTimeFormatter::DATE_TIME_FORMAT);
        $data[] = [$startDateTime, $endDateTime];

        // count events per clinic
        $count = [];
        /** @var Clinic[] $clinicLookup */
        $clinicLookup = [];
        foreach ($events as $event) {
            $clinicId = $event->getClinic()->getId();
            if (!isset($count[$clinicId])) {
                $count[$clinicId] = 1;
                $clinicLookup[$clinicId] = $event->getClinic();
            } else {
                ++$count[$clinicId];
            }
        }

        // export
        foreach ($clinicLookup as $id => $clinic) {
            $data[] = [$clinic->getName(), $count[$id]];
        }
        $data[] = [];

        return $data;
    }

    /**
     * @return string[]
     */
    private function getEventsHeader(TranslatorInterface $translator)
    {
        $start = $translator->trans('start_date_time', [], 'trait_start_end');
        $end = $translator->trans('end_date_time', [], 'trait_start_end');
        $clinic = $translator->trans('entity.name', [], 'entity_clinic');
        $person = $translator->trans('entity.plural', [], 'entity_event_tag');

        return [$start, $end, $clinic, $person];
    }
}
