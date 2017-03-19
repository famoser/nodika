<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/12/2016
 * Time: 12:05
 */

namespace AppBundle\DataFixtures\ORM\Base;


use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

abstract class BaseFixture extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @return \Faker\Generator
     */
    protected function getFaker()
    {
        return Factory::create("de_CH");
    }

    /**
     * create an instance with all random values
     *
     * @return mixed
     */
    protected abstract function getAllRandomInstance();

    /**
     * @param object|User $obj
     */
    protected function fillRandomAddress(&$obj)
    {
        $faker = $this->getFaker();
        $obj->setAddressCountry($faker->countryCode);
        $obj->setAddressLocality($faker->city);
        $obj->setStreetAddress($faker->streetAddress);
        $obj->setPostalCode($faker->postcode);
    }

    /**
     * create random instances
     *
     * @param $count
     * @param ObjectManager $manager
     */
    protected function loadSomeRandoms(ObjectManager $manager, $count = 5)
    {
        for ($i = 0; $i < $count; $i++) {
            $instance = $this->getAllRandomInstance();
            $manager->persist($instance);
        }
    }
}