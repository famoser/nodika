<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventGeneration;

use App\Entity\Event;
use App\Entity\EventGenerationTargetClinic;
use App\Entity\EventGenerationTargetDoctor;
use App\Entity\Traits\EventTrait;

class ConflictLookup
{
    private $lookup;

    /**
     * ConflictLookup constructor.
     *
     * @param EventTrait[] $events
     */
    public function __construct(array $events, \DateInterval $bufferSize)
    {
        // setup db
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec('DROP TABLE IF EXISTS conflicts');
        $pdo->exec('CREATE TABLE conflicts (start TEXT, end TEXT, doctor_id INTEGER, clinic_id INTEGER)');
        $this->lookup = $pdo;

        $this->addEvents($events, $bufferSize);
    }

    /**
     * @param EventTrait[] $events
     */
    public function addEvents(array $events, \DateInterval $bufferSize)
    {
        // convert events to insert array
        $insertArray = [];
        foreach ($events as $event) {
            $entry = [];
            $entry[] = (clone $event->getStartDateTime())->sub($bufferSize)->format('c');
            $entry[] = (clone $event->getEndDateTime())->add($bufferSize)->format('c');
            $entry[] = $event->getDoctor() ? $event->getDoctor()->getId() : 0;
            $entry[] = $event->getClinic() ? $event->getClinic()->getId() : 0;
            $insertArray[] = $entry;
        }

        // batch insert
        $insertSize = \count($insertArray);
        $batchSize = 100;
        for ($i = 0; $i < $insertSize; $i += $batchSize) {
            $currentBatchSize = min($insertSize, $i + $batchSize);

            // build up SQL after VALUES & create parameters array
            $rowSqls = [];
            $parameters = [];
            $fields = ['start', 'end', 'doctor_id', 'clinic_id'];
            $fieldLength = \count($fields);
            for ($j = $i; $j < $currentBatchSize; ++$j) {
                $variableNames = [];
                for ($k = 0; $k < $fieldLength; ++$k) {
                    $variableName = $fields[$k].$j;
                    $parameters[$variableName] = $insertArray[$j][$k];
                    $variableNames[] = ':'.$variableName;
                }
                $rowSqls[] = '('.implode(',', $variableNames).')';
            }

            // insert
            $sql = 'INSERT INTO conflicts (start, end, doctor_id, clinic_id) VALUES '.implode(',', $rowSqls);
            // potential improvement: prepared could be reused over iterations
            $prepared = $this->lookup->prepare($sql);
            $prepared->execute($parameters);
        }

        // (re)create indexes for fast queries
        $this->lookup->exec('CREATE INDEX doctor_index ON conflicts(start, end, doctor_id)');
        $this->lookup->exec('CREATE INDEX clinic_index ON conflicts(start, end, clinic_id)');
    }

    /**
     * @param EventTrait $event
     *
     * @return bool
     */
    public function hasConflict(EventTarget $eventTarget, $event)
    {
        $parameters = [];
        $sql = 'SELECT COUNT(*) FROM conflicts WHERE ';
        $constraints = [];

        // detect conflicts
        $conflictCases = [
            '(start < :start0 AND end > :end0)', // case conflict fully contained in event
            '(start < :start1 AND end > :start2)', // case conflict overlaps left
            '(start < :end1 AND start > :end2)', // case conflict overlaps right
        ];
        $constraints[] = '('.implode(' OR ', $conflictCases).')';

        // fill parameters
        $startStr = $event->getStartDateTime()->format('c');
        $endStr = $event->getEndDateTime()->format('c');
        for ($i = 0; $i < 3; ++$i) {
            $parameters['start'.$i] = $startStr;
            $parameters['end'.$i] = $endStr;
        }

        // filter by impacted query
        if (null !== $eventTarget->getClinic()) {
            /* @var EventGenerationTargetClinic $eventTarget */
            $constraints[] = '(clinic_id = :clinic_id)';
            $parameters['clinic_id'] = $eventTarget->getClinic()->getId();
        } elseif (null !== $eventTarget->getDoctor()) {
            /* @var EventGenerationTargetDoctor $eventTarget */
            $constraints[] = '(doctor_id = :doctor_id)';
            $parameters['doctor_id'] = $eventTarget->getDoctor()->getId();
        }

        $sql .= implode(' AND ', $constraints);

        // execute
        $prepared = $this->lookup->prepare($sql);
        $prepared->execute($parameters);

        return $prepared->fetchColumn() > 0;
    }
}
