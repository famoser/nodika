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
use App\Entity\EventOffer;
use App\Entity\EventOfferEntry;
use App\Entity\Member;
use App\Enum\EventChangeType;
use App\Enum\OfferStatus;
use App\Enum\TradeTag;
use App\Helper\DateTimeConverter;
use App\Model\Event\SearchModel;
use App\Service\EmailService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/offer")
 * @Security("has_role('ROLE_USER')")
 */
class OfferController extends BaseFormController
{
    /**
     * @Route("/", name="offer_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $repo = $this->getDoctrine()->getRepository('App:EventOffer');
        $arr['author_of_offers'] = $repo->findBy(['offeredByMember' => $member->getId(), 'offeredByPerson' => $this->getPerson()->getId()]);
        $arr['accepted_offers'] = $repo->findBy(['offeredToMember' => $member->getId(), 'offeredToPerson' => $this->getPerson()->getId(), 'status' => OfferStatus::ACCEPTED]);
        $arr['open_offers'] = $repo->findBy(['offeredToMember' => $member->getId(), 'offeredToPerson' => $this->getPerson()->getId(), 'status' => OfferStatus::OPEN]);

        return $this->render('offer/index.html.twig', $arr);
    }
}
