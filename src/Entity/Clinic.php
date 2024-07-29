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
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\InvitationTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\ThingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Clinic is part of the organisation, and is responsible for the events assigned to it.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ClinicRepository")
 *
 * @ORM\HasLifecycleCallbacks
 */
class Clinic extends BaseEntity
{
    use AddressTrait;
    use CommunicationTrait;
    use IdTrait;
    use InvitationTrait;
    use SoftDeleteTrait;
    use ThingTrait;

    /**
     * @var Doctor[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Doctor", mappedBy="clinics")
     *
     * @ORM\OrderBy({"familyName" = "ASC", "givenName" = "ASC"})
     */
    private $doctors;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="clinic")
     *
     * @ORM\OrderBy({"startDateTime" = "ASC"})
     */
    private $events;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->doctors = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**
     * Get persons.
     *
     * @return \Doctrine\Common\Collections\Collection|Doctor[]
     */
    public function getDoctors()
    {
        return $this->doctors;
    }

    /**
     * Get events.
     *
     * @return \Doctrine\Common\Collections\Collection|Event[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    public function addDoctor(Doctor $doctor)
    {
        $this->getDoctors()->add($doctor);
        $doctor->getClinics()->add($this);
    }

    public function removeDoctor(Doctor $doctor)
    {
        $this->getDoctors()->removeElement($doctor);
        $doctor->getClinics()->removeElement($this);
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return Doctor[]
     */
    public function getActiveDoctors()
    {
        $res = [];
        foreach ($this->getDoctors() as $doctor) {
            if (!$doctor->isDeleted()) {
                $res[] = $doctor;
            }
        }

        return $res;
    }
}
