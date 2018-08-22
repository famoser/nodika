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
use App\Entity\Clinic;
use App\Entity\Doctor;
use Doctrine\Common\Persistence\ObjectManager;

class LoadClinic extends BaseFixture
{
    const ORDER = LoadDoctor::ORDER + 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $realExamples = [
            ["kleintierpraxis-baselwest"],
            ["Kleintierpraxis am Ring"],
            ["VET 4 PET"],
            ["Tierklinik Rossweid"],
            ["Tierarztpraxis Haerer"],
            ["Tierarztpraxis Stebler"],
            ["Aloha Kleintierpraxis"],
            ["Zentrum Frohwies"]
        ];

        $users = $manager->getRepository(Doctor::class)->findAll();

        $clinics = [];
        foreach ($realExamples as $realExample) {
            $clinic = $this->getRandomInstance();
            $clinic->setName($realExample[0]);
            $manager->persist($clinic);
            $clinics[] = $clinic;
        }

        $userIndex = 0;
        $clinicIndex = 0;
        $allClinicsSeen = 0;
        $allUsersSeen = 0;


        $advanceWithProbability = function() {
            return rand(0, 10) > 2;
        };
        while (true) {
            $users[$userIndex]->getClinics()->add($clinics[$clinicIndex]);

            if ($advanceWithProbability) {
                $userIndex++;
            }
            $clinicIndex++;

            if ($userIndex == count($users)) {
                $userIndex = 0;
                $allUsersSeen++;
            }

            if ($clinicIndex == count($clinics)) {
                $clinicIndex = 0;
                $allClinicsSeen++;
            }

            if ($allClinicsSeen > 1 && $allUsersSeen > 1) {
                break;
            }
        }

        $manager->flush();
    }

    /**
     * create an instance with all random values.
     *
     * @return Clinic
     */
    protected function getRandomInstance()
    {
        $clinic = new Clinic();
        $this->fillCommunication($clinic);
        $this->fillAddress($clinic);
        $this->fillThing($clinic);

        return $clinic;
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
}
