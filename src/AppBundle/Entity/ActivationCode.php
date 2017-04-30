<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.02.2017
 * Time: 19:54
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Base\FileEntity;
use AppBundle\Entity\Traits\IdTrait;
use Jkweb\Bundle\CmsBundle\Entity\Interfaces\IIdentifiable;
use Jkweb\Bundle\CmsBundle\Entity\Traits\IdentifierTrait;
use Jkweb\Bundle\CmsBundle\Entity\Traits\ThingTrait;
use Jkweb\Bundle\CmsBundle\Entity\Traits\TimestampTrait;
use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Enum\Base\BaseEnum;
use AppBundle\Enum\OfferStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Jkweb\Bundle\CmsBundle\Enum\PageType;
use Jkweb\Bundle\CmsBundle\Helper\RouteConverter;


/**
 * The activation code is used to enable a new organisation to register
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ActivationCodeRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ActivationCode extends BaseEntity
{
    use IdTrait;

    /**
     * @ORM\Column(type="text")
     */
    private $code;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="activationCodes")
     */
    private $organisation;
}
