<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 30/04/2017
 * Time: 15:51
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Base\BaseEntity;
use AppBundle\Entity\Traits\CommunicationTrait;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\PersonTrait;
use AppBundle\Enum\NewsletterChoice;
use AppBundle\Helper\NamingHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * A Newsletter is a person subscribed (or not) to receive news about this application
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsletterRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Newsletter extends BaseEntity
{
    use IdTrait;
    use PersonTrait;
    use CommunicationTrait;

    /**
     * @ORM\Column(type="integer")
     */
    private $choice = NewsletterChoice::QUESTION;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    /**
     * Set choice
     *
     * @param integer $choice
     *
     * @return Newsletter
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;

        return $this;
    }

    /**
     * Get choice
     *
     * @return integer
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Newsletter
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * returns a string representation of this entity
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getEmail();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     * @return FormBuilderInterface
     */
    protected function getBuilder(FormBuilderInterface $builder, $defaultArray)
    {
        $builderArray = $this->getTranslationDomainForBuilder() + $defaultArray;
        $builder->add(
            "message",
            TextareaType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("message")
        );
        return $builder;
    }
}
