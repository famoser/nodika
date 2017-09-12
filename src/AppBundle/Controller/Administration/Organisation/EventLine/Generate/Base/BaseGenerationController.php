<?php

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 19/05/2017
 * Time: 19:08
 */

namespace AppBundle\Controller\Administration\Organisation\EventLine\Generate\Base;


use AppBundle\Controller\Base\BaseController;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventLine;
use AppBundle\Entity\EventLineGeneration;
use AppBundle\Entity\Member;
use AppBundle\Entity\Organisation;
use AppBundle\Enum\DistributionType;
use AppBundle\Enum\NodikaStatusCode;
use AppBundle\Form\EventLineGeneration\Nodika\ChoosePeriodType;
use AppBundle\Model\EventLineGeneration\Base\BaseConfiguration;
use AppBundle\Model\EventLineGeneration\Base\EventLineConfiguration;
use AppBundle\Model\EventLineGeneration\GenerationResult;
use AppBundle\Model\EventLineGeneration\Nodika\MemberConfiguration;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaConfiguration;
use AppBundle\Model\EventLineGeneration\Nodika\NodikaOutput;
use AppBundle\Security\Voter\EventLineGenerationVoter;
use AppBundle\Security\Voter\EventLineVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     */
    protected function addEventLineConfiguration(BaseConfiguration $configuration, Organisation $organisation)
    {
        /* @var EventLine[] $eventLineById */
        $eventLineById = [];
        foreach ($organisation->getEventLines() as $member) {
            $eventLineById[$member->getId()] = $member;
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
                    foreach ($organisation->getEventLines() as $member) {
                        $eventLineById[$member->getId()] = $member;
                    }
                }
                //set events
                $item->setEvents($eventLineById[$item->id]->getEvents());
                $item->eventsSet = true;
            }
        }
    }
}