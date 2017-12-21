<?php

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace App\Controller\Administration\Organisation\EventLine\Generate\Base;


use App\Controller\Base\BaseController;
use App\Entity\EventLine;
use App\Entity\EventLineGeneration;
use App\Entity\Organisation;
use App\Model\EventLineGeneration\Base\BaseConfiguration;
use App\Model\EventLineGeneration\Base\EventLineConfiguration;
use App\Model\EventLineGeneration\GenerationResult;

class BaseGenerationController extends BaseController
{
    /**
     * @param EventLineGeneration $generation
     * @return GenerationResult
     */
    protected function getGenerationResult(EventLineGeneration $generation)
    {
        return new GenerationResult(json_decode($generation->getGenerationResultJson()));
    }

    /**
     * @param BaseConfiguration $configuration
     * @param Organisation $organisation
     * @param EventLineGeneration $eventLineGeneration
     */
    protected function addEventLineConfiguration(BaseConfiguration $configuration, Organisation $organisation, EventLineGeneration $eventLineGeneration)
    {
        /* @var EventLine[] $eventLineById */
        $eventLineById = [];
        foreach ($organisation->getEventLines() as $eventLine) {
            if ($eventLine->getId() != $eventLineGeneration->getEventLine()->getId()) {
                $eventLineById[$eventLine->getId()] = $eventLine;
            }
        }


        $removeKeys = [];
        foreach ($configuration->eventLineConfiguration as $key => $value) {
            if (isset($eventLineById[$value->id])) {
                $value->name = $eventLineById[$value->id]->getName();
                unset($eventLineById[$value->id]);
            } else {
                $removeKeys[] = $key;
            }
        }

        foreach ($removeKeys as $removeKey) {
            unset($configuration->eventLineConfiguration[$removeKey]);
        }

        foreach ($eventLineById as $item) {
            $newConfig = EventLineConfiguration::createFromEventLine($item);
            $configuration->eventLineConfiguration[] = $newConfig;
        }

        //empty array
        $eventLineById = [];

        //add events if applicable
        foreach ($configuration->eventLineConfiguration as $item) {
            if ($item->isEnabled && !$item->eventsSet) {
                if (count($eventLineById) == 0) {
                    //cache event lines again
                    foreach ($organisation->getEventLines() as $eventLine) {
                        $eventLineById[$eventLine->getId()] = $eventLine;
                    }
                }
                //set events
                $item->setEvents($eventLineById[$item->id]->getEvents());
                $item->eventsSet = true;
            }
        }
    }
}