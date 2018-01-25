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
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\PersonTrait;
use App\Enum\NewsletterChoice;
use App\Helper\NamingHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * A Newsletter is a person subscribed (or not) to receive news about this application.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\NewsletterRepository")
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
     * Set choice.
     *
     * @param int $choice
     *
     * @return Newsletter
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;

        return $this;
    }

    /**
     * Get choice.
     *
     * @return int
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * Set message.
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
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * returns a string representation of this entity.
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
     *
     * @return FormBuilderInterface
     */
    protected function getBuilder(FormBuilderInterface $builder, $defaultArray)
    {
        $builderArray = $this->getTranslationDomainForBuilder() + $defaultArray;
        $builder->add(
            'message',
            TextareaType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('message')
        );

        return $builder;
    }
}
