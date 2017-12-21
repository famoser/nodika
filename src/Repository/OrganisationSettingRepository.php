<?php

namespace App\Repository;

use App\Entity\Organisation;
use App\Entity\OrganisationSetting;
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
        $result->setReceiverOfRemainders($organisation->getLeaders()->count() > 0 ? $organisation->getLeaders()->first() : null);
        $this->getEntityManager()->persist($result);
        $this->getEntityManager()->flush();
        return $result;
    }
}
