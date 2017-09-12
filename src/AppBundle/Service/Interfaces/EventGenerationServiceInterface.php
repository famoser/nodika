<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 18:23
 */

namespace AppBundle\Service\Interfaces;


use AppBundle\Entity\EventLineGeneration;
use AppBundle\Model\EventLineGeneration\GenerationResult;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaConfiguration;
use AppBundle\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use AppBundle\Model\EventLineGeneration\RoundRobin\RoundRobinOutput;

interface EventGenerationServiceInterface
{
    /**
     * tries to generate the events
     * returns true if successful
     *
     * @param RoundRobinConfiguration $roundRobinConfiguration
     * @param callable $memberAllowedCallable with arguments $startDateTime, $endDateTime, $member which returns a boolean if the event can happen
     * @return RoundRobinOutput
     */
    public function generateRoundRobin(RoundRobinConfiguration $roundRobinConfiguration, $memberAllowedCallable);

    /**
     * persist the events associated with this generation in the database
     *
     * @param EventLineGeneration $generation
     * @param GenerationResult $generationResult
     * @return bool
     */
    public function persist(EventLineGeneration $generation, GenerationResult $generationResult);

    /**
     * @param NodikaConfiguration $nodikaConfiguration
     * @return bool
     */
    public function setEventTypeDistribution(NodikaConfiguration $nodikaConfiguration);
}