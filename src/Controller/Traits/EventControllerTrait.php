<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 4/1/18
 * Time: 11:23 AM
 */

namespace App\Controller\Traits;


use App\Entity\Member;
use App\Entity\Person;
use App\Helper\DateTimeFormatter;
use App\Model\Event\SearchEventModel;
use App\Model\EventLine\EventLineModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

trait EventControllerTrait
{
    /**
     * @param Request $request
     * @param Member $member
     *
     * @return SearchEventModel
     * @throws \Exception
     */
    private function resolveSearchEventModel(Request $request, Member $member)
    {
        $organisation = $member->getOrganisation();

        $startQuery = $request->query->get('start');
        $startDateTime = new \DateTime($startQuery);

        $endQuery = $request->query->get('end');
        $endDateTime = false;
        if (mb_strlen($endQuery) > 0) {
            $endDateTime = new \DateTime($endQuery);
        }
        if (!$endDateTime) {
            $endDateTime = clone $startDateTime;
            $endDateTime = $endDateTime->add(new \DateInterval('P1Y'));
        }

        $memberQuery = $request->query->get('member');
        $member = null;
        if (is_numeric($memberQuery)) {
            $memberQueryInt = (int)$memberQuery;
            foreach ($organisation->getMembers() as $organisationMember) {
                if ($organisationMember->getId() === $memberQueryInt) {
                    $member = $organisationMember;
                }
            }
        }

        $eventLineQuery = $request->query->get('event_line');
        $eventLine = null;
        if (is_numeric($eventLineQuery)) {
            $eventLineInt = (int)$eventLineQuery;
            foreach ($organisation->getEventLines() as $organisationEventLine) {
                if ($organisationEventLine->getId() === $eventLineInt) {
                    $eventLine = $organisationEventLine;
                }
            }
        }

        $personQuery = $request->query->get('person');
        $person = null;
        if (is_numeric($personQuery)) {
            $personQueryInt = (int)$personQuery;
            foreach ($organisation->getMembers() as $organisationMember) {
                foreach ($organisationMember->getPersons() as $organisationPerson) {
                    if ($organisationPerson->getId() === $personQueryInt) {
                        $person = $organisationPerson;
                    }
                }
            }
        }

        $searchEventModel = new SearchEventModel($organisation, $startDateTime);
        $searchEventModel->setEndDateTime($endDateTime);
        $searchEventModel->setMember($member);
        $searchEventModel->setEventLine($eventLine);
        $searchEventModel->setFrontendUser($person);

        return $searchEventModel;
    }

    /**
     * @param EventLineModel[] $eventModels
     * @param TranslatorInterface $translator
     *
     * @return string[]
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
                if ($event->getFrontendUser() instanceof Person) {
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
        $person = $translator->trans('entity.name', [], 'entity_person');

        return [$start, $end, $member, $person];
    }
}