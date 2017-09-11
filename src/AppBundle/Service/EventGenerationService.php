<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11/09/2017
 * Time: 18:24
 */

namespace AppBundle\Service;


use AppBundle\Entity\EventLineGeneration;
use AppBundle\Enum\RoundRobinStatusCode;
use AppBundle\Helper\StaticMessageHelper;
use AppBundle\Model\EventLineGeneration\GeneratedEvent;
use AppBundle\Model\EventLineGeneration\GenerationResult;
use AppBundle\Model\EventLineGeneration\RoundRobin\MemberConfiguration;
use AppBundle\Model\EventLineGeneration\RoundRobin\RoundRobinConfiguration;
use AppBundle\Model\EventLineGeneration\RoundRobin\RoundRobinOutput;
use AppBundle\Service\Interfaces\EventGenerationServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class EventGenerationService implements EventGenerationServiceInterface
{
    /* @var RegistryInterface $doctrine */
    private $doctrine;

    /* @var TranslatorInterface $translator */
    private $translator;

    /* @var Session $session */
    private $session;

    public function __construct($doctrine, $translator, $session)
    {
        $this->doctrine = $doctrine;
        $this->translator = $translator;
        $this->session = $session;
    }

    /**
     * @param $message
     */
    private function displayError($message)
    {
        $this->session->getFlashBag()->set(StaticMessageHelper::FLASH_ERROR, $message);
    }

    /**
     * @param $message
     */
    private function displaySuccess($message)
    {
        $this->session->getFlashBag()->set(StaticMessageHelper::FLASH_SUCCESS, $message);
    }

    /**
     * @param RoundRobinOutput $roundRobinResult
     * @param int $status
     * @return RoundRobinOutput
     */
    private function returnRoundRobinError(RoundRobinOutput $roundRobinResult, $status)
    {
        $this->displayError(
            $this->translator->trans(
                RoundRobinStatusCode::getTranslation($status),
                [],
                RoundRobinStatusCode::getTranslationDomainStatic()
            )
        );

        $roundRobinResult->roundRobinStatusCode = $status;
        return $roundRobinResult;
    }

    /**
     * @param RoundRobinOutput $roundRobinResult
     * @return RoundRobinOutput
     */
    private function returnRoundRobinSuccess(RoundRobinOutput $roundRobinResult)
    {
        $status = RoundRobinStatusCode::SUCCESSFUL;
        $this->displaySuccess(
            $this->translator->trans(
                RoundRobinStatusCode::getTranslation($status),
                [],
                RoundRobinStatusCode::getTranslationDomainStatic()
            )
        );

        $roundRobinResult->roundRobinStatusCode = $status;
        return $roundRobinResult;
    }

    /**
     * tries to generate the events
     * returns true if successful
     *
     * @param RoundRobinConfiguration $roundRobinConfiguration
     * @param callable $memberAllowedCallable with arguments $startDateTime, $endDateTime, $member which returns a boolean if the event can happen
     * @return RoundRobinOutput
     * @throws \Exception
     */
    public function generateRoundRobin(RoundRobinConfiguration $roundRobinConfiguration, $memberAllowedCallable)
    {
        $generationResult = new GenerationResult(null);
        $generationResult->generationDateTime = new \DateTime();

        $roundRobinResult = new RoundRobinOutput();
        $roundRobinResult->version = 1;

        /* @var MemberConfiguration[] $members */
        $members = [];
        foreach ($roundRobinConfiguration->memberConfigurations as $memberConfiguration) {
            if ($memberConfiguration->isEnabled) {
                $members[$memberConfiguration->order] = $memberConfiguration;
            }
        }
        //sorts by key
        ksort($members);
        $members = array_values($members);

        $activeIndex = 0;
        $totalMembers = count($members);
        /* @var MemberConfiguration[] $priorityQueue */
        $priorityQueue = [];
        /* @var int[] $indexesInPriorityQueue */
        $indexesInPriorityQueue = [];
        $currentDate = clone($roundRobinConfiguration->startDateTime);
        $dateIntervalAdd = "PT" . $roundRobinConfiguration->lengthInHours . "H";
        while ($currentDate < $roundRobinConfiguration->endDateTime) {
            $endDate = clone($currentDate);
            $endDate->add(new \DateInterval($dateIntervalAdd));
            //check if something in priority queue
            /* @var MemberConfiguration $matchMember */
            $matchMember = null;
            if (count($priorityQueue) > 0) {
                $i = 0;
                for (; $i < count($priorityQueue); $i++) {
                    if ($memberAllowedCallable($currentDate, $endDate, $priorityQueue[$i])) {
                        $matchMember = $priorityQueue[$i];
                        break;
                    }
                }
                if ($matchMember != null) {
                    unset($indexesInPriorityQueue[$priorityQueue[$i]->id]);
                    unset($priorityQueue[$i]);
                    //reset keys in array (0,1,2,3,4,...)
                    $priorityQueue = array_values($priorityQueue);
                }
            }
            if ($matchMember == null) {
                while (true) {
                    //wrap around index
                    if ($activeIndex >= $totalMembers) {
                        $activeIndex = 0;
                    }

                    if (isset($indexesInPriorityQueue[$members[$activeIndex]->id])) {
                        $activeIndex++;
                        continue;
                    }

                    if ($memberAllowedCallable($currentDate, $endDate, $members[$activeIndex])) {
                        $matchMember = $members[$activeIndex];
                        $activeIndex++;
                        break;
                    } else {
                        $priorityQueue[] = $members[$activeIndex];
                        $indexesInPriorityQueue[$members[$activeIndex]->id] = 1;
                        if (count($priorityQueue) == $totalMembers) {
                            return $this->returnRoundRobinError($roundRobinResult, RoundRobinStatusCode::PRIORITY_QUEUE_FULL);
                        }
                        $activeIndex++;
                    }
                }
            }

            if ($matchMember == null) {
                throw new \Exception("cannot happen!");
            } else {
                $event = new GeneratedEvent();
                $event->memberId = $matchMember->id;
                $event->startDateTime = $currentDate;
                $event->endDateTime = $endDate;
                $generationResult->events[] = $event;
            }
            $currentDate = clone($endDate);
        }

        //prepare RR result
        $roundRobinResult->endDateTime = $currentDate;
        $roundRobinResult->lengthInHours = $roundRobinConfiguration->lengthInHours;
        $roundRobinResult->memberConfiguration = $members;
        $roundRobinResult->priorityQueue = $priorityQueue;
        $roundRobinResult->activeIndex = $activeIndex;
        $roundRobinResult->indexesInPriorityQueue = $indexesInPriorityQueue;
        $roundRobinResult->generationResult = $generationResult;
        return $this->returnRoundRobinSuccess($roundRobinResult);
    }

    /**
     * persist the events associated with this generation in the database
     *
     * @param EventLineGeneration $generation
     * @return bool
     */
    public function persist(EventLineGeneration $generation)
    {
        // TODO: Implement persist() method.
    }
}