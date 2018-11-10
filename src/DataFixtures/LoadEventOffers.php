<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\DataFixtures\Base\BaseFixture;
use App\Entity\Doctor;
use App\Entity\EventGeneration;
use App\Entity\EventOffer;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventOffers extends BaseFixture
{
    const ORDER = LoadClinic::ORDER + LoadDoctor::ORDER + LoadGeneration::ORDER + 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $doctors = $manager->getRepository(Doctor::class)->findAll();
        $lastDoctor = $doctors[\count($doctors) - 1];
        foreach ($doctors as $doctor) {
            $receiverClinic = $doctor->getClinics()->first();
            $senderClinic = $lastDoctor->getClinics()->first();

            if (\count($senderClinic->getEvents()) > 0 && \count($receiverClinic->getEvents()) > 4) {
                $offer = new EventOffer();
                $offer->setReceiver($doctor);
                $offer->setReceiverClinic($receiverClinic);
                $offer->setSender($lastDoctor);
                $offer->setSenderClinic($senderClinic);
                $offer->setMessage('please accept this offer, thank you!');
                $offer->getEventsWhichChangeOwner()->add($senderClinic->getEvents()[0]);
                $offer->getEventsWhichChangeOwner()->add($senderClinic->getEvents()[1]);
                $offer->getEventsWhichChangeOwner()->add($receiverClinic->getEvents()[4]);
                $manager->persist($offer);
            }

            $lastDoctor = $doctor;
        }
        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return static::ORDER;
    }

    /**
     * create an instance with all random values.
     *
     * @return EventGeneration
     */
    protected function getRandomInstance()
    {
        $eventGeneration = new EventGeneration();
        $this->fillThing($eventGeneration);

        return $eventGeneration;
    }
}
