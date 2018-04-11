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
use App\Entity\FrontendUser;
use App\Entity\Member;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMember extends BaseFixture
{
    const ORDER = LoadFrontendUser::ORDER + 1;

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

        $users = $manager->getRepository(FrontendUser::class)->findAll();

        $inclusionProbability = count($realExamples) / 3;

        foreach ($realExamples as $realExample) {
            $member = $this->getRandomInstance();
            $member->setName($realExample[0]);
            $manager->persist($member);
            foreach ($users as $user) {
                if (rand(0, $inclusionProbability) === 0) {
                    $user->getMembers()->add($member);
                    $manager->persist($user);
                }
            }

        }

        $manager->flush();
    }

    /**
     * create an instance with all random values.
     *
     * @return Member
     */
    protected function getRandomInstance()
    {
        $member = new Member();
        $this->fillCommunication($member);
        $this->fillAddress($member);
        $this->fillThing($member);

        return $member;
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
