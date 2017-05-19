<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Person;

/**
 * PersonRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PersonRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Person $person
     * @param \DateTime $dateTime
     * @param string $comparator
     * @return array
     */
    public function getEventLinePreview(Person $person, \DateTime $dateTime, $comparator = ">")
    {
        $qb =$this->getEntityManager()->createQueryBuilder();
        return $qb->select("el")
            ->from("AppBundle:EventLine", "el")
            ->from("AppBundle:Event", "e")
            ->join("el.organisation", "o")
            ->join("o.members", "m")
            ->join("m.persons", "p")
            ->where("e.startDateTime $comparator :startDateTime")
            ->setParameter('startDateTime', $dateTime)
            ->setParameter("person", $person)
            ->getQuery()
            ->getResult();

    }
}
