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
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\Event;
use App\Entity\EventOffer;
use App\Entity\EventOfferAuthorization;
use App\Entity\EventOfferEntry;
use App\Enum\OfferStatus;
use App\Enum\AuthorizationStatus;
use App\Model\Event\SearchModel;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/trade")
 */
class TradeController extends BaseFormController
{
    /**
     * @Route("/", name="trade_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('trade/index.html.twig');
    }

    /**
     * @Route("/{eventOffer}", name="trade_accept")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function acceptAction(EventOffer $eventOffer)
    {
        $user = $this->getUser();
        foreach ($eventOffer->getAuthorizations() as $authorization) {
            if ($authorization->)
        }
    }
}
