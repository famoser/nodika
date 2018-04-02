<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use App\Helper\HashHelper;
use App\Helper\NamingHelper;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait SoftDeleteTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt = null;

    /**
     * returns if the person can be invited
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deletedAt != null;
    }

    /**
     * soft deletes
     */
    public function delete()
    {
        $this->deletedAt = new \DateTime();
    }
}
