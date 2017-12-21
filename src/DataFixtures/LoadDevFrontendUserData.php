<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/12/2016
 * Time: 11:47
 */

namespace App\DataFixtures;


use App\DataFixtures\Base\BaseFixture;
use App\Entity\FrontendUser;
use App\Entity\Person;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDevFrontendUserData extends BaseFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* @var Person $person */
        $person = $this->getReference("person-1");

        $user = FrontendUser::createFromPerson($person);
        $user->setPlainPassword('asdf1234');
        $user->persistNewPassword();

        $manager->persist($user);
        $manager->flush();
    }

    public function getOrder()
    {
        return 30;
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