<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/12/2016
 * Time: 11:47
 */

namespace AppBundle\DataFixtures\ORM\Production;


use AppBundle\DataFixtures\ORM\Base\BaseFixture;
use AppBundle\Entity\User;
use AppBundle\Enum\Genders;
use AppBundle\Enum\LeisureActivityLevel;
use AppBundle\Enum\NutritionCategory;
use AppBundle\Enum\WorkActivityLevel;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserData extends BaseFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        //add info@jkweb.ch for dev
        $user = new User();
        $user->setUsername('info@jkweb.ch');
        $user->setEmail("info@jkweb.ch");
        /* done with
        $pwUpdater = $this->get('fos_user.util.password_updater');
        $pwUpdater->hashPassword()
        */
        $user->setPassword('$2y$10$m5E0vbdaBruda1JiuUBxbOEWeGPttJwuSDHqRzm6N0a/IWf4C4WkS');
        $user->setPlainPassword('ib13izdb186d');
        $user->setLastLogin(new \DateTime());
        $user->setEnabled(true);
        $user->addRole('ROLE_ADMIN');
        $manager->persist($user);
        $manager->flush();

        $this->addReference('user-1', $user);
    }

    public function getOrder()
    {
        return 1;
    }

    /**
     * create an instance with all random values
     *
     * @return mixed
     */
    protected function getAllRandomInstance()
    {
        return null;
    }
}