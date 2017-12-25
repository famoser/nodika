<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Organisation;
use App\Entity\OrganisationSetting;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\ORMException;

/**
 * OrganisationSettingRepository.
 */
class OrganisationSettingRepository extends EntityRepository
{
    /**
     * @param Organisation $organisation
     *
     * @return OrganisationSetting
     */
    public function getByOrganisation(Organisation $organisation)
    {
        $result = $this->findOneBy(['organisation' => $organisation->getId()]);
        if ($result instanceof OrganisationSetting) {
            return $result;
        }
        $result = new OrganisationSetting();
        $result->setOrganisation($organisation);
        $result->setReceiverOfRemainders($organisation->getLeaders()->count() > 0 ? $organisation->getLeaders()->first() : null);
        $em = $this->getEntityManager();

        try {
            $em->persist($result);
            $em->flush();
        } catch (\Exception $e) {
            //we can't do anything if the database misbehaves

        }

        return $result;
    }
}
