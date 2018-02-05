<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures\Base;

use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\PersonTrait;
use App\Entity\Traits\ThingTrait;
use App\Service\EventGenerationService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseFixture extends Fixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var EventGenerationService
     */
    private $eventGenerationService;
    /* @var ContainerInterface $container */
    private $container;

    public function __construct(EventGenerationService $eventGenerationService)
    {
        $this->eventGenerationService = $eventGenerationService;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return \App\Service\EventGenerationService
     */
    protected function getEventGenerationService()
    {
        return $this->eventGenerationService;
    }

    /**
     * @param AddressTrait $obj
     */
    protected function fillRandomAddress($obj)
    {
        $faker = $this->getFaker();
        $obj->setStreet($faker->streetAddress);
        $obj->setStreetNr($faker->numberBetween(0, 300));
        if ($faker->numberBetween(0, 10) > 8) {
            $obj->setAddressLine($faker->streetAddress);
        }
        $obj->setPostalCode($faker->numberBetween(0, 9999));
        $obj->setCity($faker->city);
        $obj->setCountry($faker->countryCode);
    }

    /**
     * @return \Faker\Generator
     */
    protected function getFaker()
    {
        return Factory::create('de_CH');
    }

    /**
     * @param CommunicationTrait $obj
     */
    protected function fillRandomCommunication($obj)
    {
        $faker = $this->getFaker();
        $obj->setEmail($faker->email);
        if ($faker->numberBetween(0, 10) > 5) {
            $obj->setPhone($faker->phoneNumber);
        }
        if ($faker->numberBetween(0, 10) > 8) {
            $obj->setWebpage($faker->url);
        }
    }

    /**
     * @param ThingTrait $obj
     */
    protected function fillRandomThing($obj)
    {
        $faker = $this->getFaker();
        $obj->setName($faker->text(50));
        if ($faker->numberBetween(0, 10) > 5) {
            $obj->setDescription($faker->text(200));
        }
    }

    /**
     * @param PersonTrait $obj
     */
    protected function fillRandomPerson($obj)
    {
        $faker = $this->getFaker();
        $obj->setGivenName($faker->firstName);
        $obj->setFamilyName($faker->lastName);
        if ($faker->numberBetween(0, 10) > 5) {
            $obj->setJobTitle($faker->jobTitle);
        }
    }

    /**
     * create random instances.
     *
     * @param $count
     * @param ObjectManager $manager
     */
    protected function loadSomeRandoms(ObjectManager $manager, $count = 5)
    {
        for ($i = 0; $i < $count; ++$i) {
            $instance = $this->getAllRandomInstance();
            $manager->persist($instance);
        }
    }

    /**
     * create an instance with all random values.
     *
     * @return mixed
     */
    abstract protected function getAllRandomInstance();
}
