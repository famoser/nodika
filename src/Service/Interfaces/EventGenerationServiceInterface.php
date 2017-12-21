<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 18:23
 */

namespace App\Service\Interfaces;


use App\Entity\EventLineGeneration;
use App\Entity\Person;
use App\Model\EventLineGeneration\GenerationResult;
use App\Model\EventLineGeneration\Nodika\NodikaConfiguration;
use App\Model\EventLineGeneration\Nodika\NodikaOutput;
use App\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use App\Model\EventLineGeneration\RoundRobin\RoundRobinOutput;

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
     * tries to generate the events
     * returns true if successful
     *
     * @param NodikaConfiguration $nodikaConfiguration
     * @param callable $memberAllowedCallable with arguments $startDateTime, $endDateTime, $member which returns a boolean if the event can happen
     * @return NodikaOutput
     */
    public function generateNodika(NodikaConfiguration $nodikaConfiguration, $memberAllowedCallable);

    /**
     * persist the events associated with this generation in the database
     *
     * @param EventLineGeneration $generation
     * @param GenerationResult $generationResult
     * @param Person $person
     * @return bool
     */
    public function persist(EventLineGeneration $generation, GenerationResult $generationResult, Person $person);

    /**
     * @param NodikaConfiguration $nodikaConfiguration
     * @return bool
     */
    public function setEventTypeDistribution(NodikaConfiguration $nodikaConfiguration);
}