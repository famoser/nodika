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
use App\Entity\Event;
use App\Entity\FrontendUser;
use App\Model\Event\SearchModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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

        $events = $this->getDoctrine()->getRepository(Event::class)->search($searchEventModel);

        $arr["events"] = $events;
        return $this->render('assign/index.html.twig', $arr);
    }

    /**
     * @Route("/api/assignable_users", name="assign_assignable_users")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function apiAssignableUsersAction(SerializerInterface $serializer)
    {
        $result = [];

        $user = $this->getUser();
        foreach ($user->getMembers() as $member) {
            foreach ($member->getFrontendUsers() as $frontendUser) {
                $result[$frontendUser->getId()] = $frontendUser;
            }
        }

        $result = array_values($result);

        return new JsonResponse($serializer->serialize($result, "json", ["attributes" => ["fullName", "members" => ["name"]]]), 200, [], true);
    }
}
