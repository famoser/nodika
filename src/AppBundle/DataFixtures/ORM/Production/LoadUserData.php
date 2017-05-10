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
        $user = new FrontendUser();
        $user->setEmail("info@nodika.ch");
        $user->setPlainPassword('87128dg1889gfd6f2hag');
        $user->hashAndRemovePlainPassword();
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