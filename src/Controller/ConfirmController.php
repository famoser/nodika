<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseFormController;
use App\Controller\Traits\EventControllerTrait;
use App\Entity\Event;
use App\Entity\EventLine;
use App\Entity\EventPast;
use App\Entity\Settings;
use App\Enum\EventChangeType;
use App\Model\Event\SearchModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/confirm")
 * @Security("has_role('ROLE_USER')")
 */
class ConfirmController extends BaseFormController
{
    use EventControllerTrait;

    /**
     * @Route("/", name="confirm_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $searchModel = new SearchModel();
        $searchModel->setMembers($this->getUser()->getMembers());
        $searchModel->setStartDateTime(new \DateTime());

        $eventLineModels = $this->getDoctrine()->getRepository(EventLine::class)->findEventLineModels($searchModel);

        $arr["event_line_models"] = $eventLineModels;

        return $this->render('event/search.html.twig', $arr);
    }

    /**
     * @Route("/{event}", name="confirm_event")
     *
     * @param Event $event
     *
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function eventAction(Event $event, TranslatorInterface $translator)
    {
        $event->setConfirmDateTime(new \DateTime());
        $eventPast = new EventPast($event, EventChangeType::CONFIRMED_BY_PERSON, $this->getUser());
        $this->fastSave($eventPast, $event);
        $this->displaySuccess($translator->trans('confirm.messages.confirm_successful', [], 'event'));
        return $this->redirectToRoute('confirm_index');
    }

    /**
     * @Route("/all", name="confirm_all")
     *
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function allAction(TranslatorInterface $translator)
    {
        $setting = $this->getDoctrine()->getRepository(Settings::class)->findSingle();

        $searchModel = new SearchModel();
        $searchModel->setIsConfirmed(false);
        $searchModel->setFrontendUser($this->getUser());
        $end = new \DateTime();
        $end->add(new \DateInterval("P" . $setting->getConfirmDaysAdvance()."T"));
        $searchModel->setStartDateTime(new \DateTime());
        $searchModel->setEndDateTime($end);

        $eventLines = $this->getDoctrine()->getRepository('App:EventLine')->findEventLineModels($searchModel);

        $manager = $this->getDoctrine()->getManager();
        $total = 0;
        foreach ($eventLines as $eventLine) {
            foreach ($eventLine->events as $event) {
                $event->setConfirmDateTime(new \DateTime());
                $eventPast = new EventPast($event, EventChangeType::CONFIRMED_BY_PERSON, $this->getUser());
                $manager->persist($event);
                $manager->persist($eventPast);
                $total++;
            }
        }
        $manager->flush();

        $this->displaySuccess($translator->trans('confirm.messages.confirm_all_successful', ['%count%' => $total], 'event'));

        return $this->redirectToRoute('confirm_index');
    }
}
