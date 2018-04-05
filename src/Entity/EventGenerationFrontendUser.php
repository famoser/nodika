<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\ChangeAwareTrait;
use App\Entity\Traits\EventGenerationTarget;
use App\Entity\Traits\IdTrait;
use App\Enum\DistributionType;
use App\Helper\DateTimeFormatter;
use App\Model\EventLineGeneration\Base\BaseConfiguration;
use App\Model\EventLineGeneration\Base\BaseOutput;
use App\Model\EventLineGeneration\GenerationResult;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An EventGenerationMember specifies additional properties for a member
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class EventGenerationFrontendUser extends BaseEntity
{
    use IdTrait;
    use EventGenerationTarget;

    /**
     * @var FrontendUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\FrontendUser")
     */
    private $frontendUser;

    /**
     * @var EventGeneration
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EventGeneration", inversedBy="frontendUsers")
     */
    private $eventGeneration;

    /**
     * @return FrontendUser
     */
    public function getFrontendUser(): FrontendUser
    {
        return $this->frontendUser;
    }

    /**
     * @param FrontendUser $frontendUser
     */
    public function setFrontendUser(FrontendUser $frontendUser): void
    {
        $this->frontendUser = $frontendUser;
    }

    /**
     * @return EventGeneration
     */
    public function getEventGeneration(): EventGeneration
    {
        return $this->eventGeneration;
    }

    /**
     * @param EventGeneration $eventGeneration
     */
    public function setEventGeneration(EventGeneration $eventGeneration): void
    {
        $this->eventGeneration = $eventGeneration;
    }
}
