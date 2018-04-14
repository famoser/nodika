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
use App\Enum\EventChangeType;
use App\Form\Model\Event\SearchType;
use App\Helper\DateTimeFormatter;
use App\Model\Event\SearchModel;
use App\Service\Interfaces\CsvServiceInterface;
use App\Service\Interfaces\SettingServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/assign")
 * @Security("has_role('ROLE_USER')")
 */
class AssignController extends BaseFormController
{
    /**
     * @Route("/", name="assign_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAction()
    {
        $searchEventModel = new SearchModel();
        $searchEventModel->setMembers($this->getUser()->getMembers());
        $searchEventModel->setStartDateTime(new \DateTime());

        $events = $this->getDoctrine()->getRepository(EventLine::class)->findEventLineModels($searchEventModel);

        $arr["events"] = $events;
        return $this->render('assign/index.html.twig', $arr);
    }
}
