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
use App\Entity\Settings;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSettings extends BaseFixture
{
    const ORDER = 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $setting = new Settings();
        $setting->setFrontendUserName("Mitarbeiter");
        $setting->setMemberName("Praxis");
        $setting->setOrganisationName("knbu.ch");
        $setting->setSupportMail('support@famoser.ch');
        $setting->setConfirmDaysAdvance(10);
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
