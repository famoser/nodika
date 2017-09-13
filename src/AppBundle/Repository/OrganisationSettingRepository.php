<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\OrganisationSetting;
use Doctrine\ORM\EntityRepository;

/**
 * OrganisationSettingRepository
 */
class OrganisationSettingRepository extends EntityRepository
{
    /**
     * @param Organisation $organisation
     * @return OrganisationSetting
     */
    public function getByOrganisation(Organisation $organisation)
    {
        $result = $this->findOneBy(["organisation" => $organisation->getId()]);
        if ($result instanceof OrganisationSetting) {
            return $result;
        }
        $result = new OrganisationSetting();
        $result->setOrganisation($organisation);
        $this->getEntityManager()->persist($result);
        $this->getEntityManager()->flush();
        return $result;
    }
}
