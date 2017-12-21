<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/12/2016
 * Time: 11:47
 */

namespace App\DataFixtures\Production;


use App\DataFixtures\Base\BaseFixture;
use App\Entity\AdminUser;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAdminUserData extends BaseFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new AdminUser();
        $user->setEmail("info@nodika.ch");
        $user->setPlainPassword('jhagfgawefgajwef');
        $user->persistNewPassword();
        $user->setRegistrationDate(new \DateTime());
        $user->setIsActive(true);
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