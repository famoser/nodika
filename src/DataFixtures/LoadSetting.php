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
use App\Entity\Setting;
use Doctrine\Persistence\ObjectManager;

class LoadSetting extends BaseFixture
{
    public const ORDER = 1;

    /**
     * Load data fixtures with the passed EntityManager.
     */
    public function load(ObjectManager $manager): void
    {
        $setting = new Setting();
        $setting->setCanConfirmDaysAdvance(30);
        $setting->setMustConfirmDaysAdvance(3);
        $setting->setSendRemainderDaysInterval(1);
        $manager->persist($setting);
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
}
