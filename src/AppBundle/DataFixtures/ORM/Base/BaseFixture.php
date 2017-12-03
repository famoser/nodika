<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/12/2016
 * Time: 12:05
 */

namespace AppBundle\DataFixtures\ORM\Base;


use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\PersonTrait;
use AppBundle\Entity\Traits\ThingTrait;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @return \Faker\Generator
     */
    protected function getFaker()
    {
        return Factory::create("de_CH");
    }

    /* @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return \AppBundle\Service\EventGenerationService
     */
    protected function getEventGenerationService()
    {
        return $this->container->get("app.event_generation_service");
    }

    /**
     * create an instance with all random values
     *
     * @return mixed
     */
    protected abstract function getAllRandomInstance();

    /**
     * @param AddressTrait $obj
     */
    protected function fillRandomAddress($obj)
    {
        $faker = $this->getFaker();
        $obj->setStreet($faker->streetAddress);
        $obj->setStreetNr($faker->numberBetween(0, 300));
        if ($faker->numberBetween(0, 10) > 8)
            $obj->setAddressLine($faker->streetAddress);
        $obj->setPostalCode($faker->postcode);
        $obj->setCity($faker->city);
        $obj->setCountry($faker->countryCode);
    }

    /**
     * @param CommunicationTrait $obj
     */
    protected function fillRandomCommunication($obj)
    {
        $faker = $this->getFaker();
        $obj->setEmail($faker->email);
        if ($faker->numberBetween(0, 10) > 5)
            $obj->setPhone($faker->phoneNumber);
        if ($faker->numberBetween(0, 10) > 8)
            $obj->setWebpage($faker->url);
    }

    /**
     * @param ThingTrait $obj
     */
    protected function fillRandomThing($obj)
    {
        $faker = $this->getFaker();
        $obj->setName($faker->text(50));
        if ($faker->numberBetween(0, 10) > 5)
            $obj->setDescription($faker->text(200));
    }

    /**
     * @param PersonTrait $obj
     */
    protected function fillRandomPerson($obj)
    {
        $faker = $this->getFaker();
        $obj->setGivenName($faker->firstName);
        $obj->setFamilyName($faker->lastName);
        if ($faker->numberBetween(0, 10) > 5)
            $obj->setJobTitle($faker->jobTitle);
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