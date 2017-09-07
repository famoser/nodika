<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/12/2016
 * Time: 11:47
 */

namespace AppBundle\DataFixtures\ORM\Production;


use AppBundle\DataFixtures\ORM\Base\BaseFixture;
use AppBundle\Entity\FrontendUser;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDevUserData extends BaseFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* @var FrontendUser $user */
        $user = $this->getReference('user-1');

        $user->setPlainPassword('asdf1234');
        $user->persistNewPassword();

        $manager->persist($user);
        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
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