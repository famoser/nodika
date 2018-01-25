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

use App\Helper\NamingHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait CommunicationTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $phone;

    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Url()
     */
    private $webpage;

    /**
     * @return mixed
     */
    public function getWebpage()
    {
        return $this->webpage;
    }

    /**
     * @param mixed $webpage
     */
    public function setWebpage($webpage)
    {
        $this->webpage = $webpage;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     *
     * @return static
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return static
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * get non-empty communication lines.
     *
     * @return string[]
     */
    public function getCommunicationLines()
    {
        $res = [];
        if (mb_strlen($this->getPhone()) > 0) {
            $res[] = $this->getPhone();
        }
        if (mb_strlen($this->getEmail()) > 0) {
            $res[] = $this->getEmail();
        }
        if (mb_strlen($this->getWebpage()) > 0) {
            $res[] = $this->getWebpage();
        }

        return $res;
    }

    /**
     * get the communication identifier.
     *
     * @return string
     */
    protected function getCommunicationIdentifier()
    {
        return implode(',', $this->getCommunicationLines());
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     *
     * @return FormBuilderInterface
     */
    public static function getCommunicationBuilder(FormBuilderInterface $builder, $defaultArray = [])
    {
        $builderArray = ['translation_domain' => NamingHelper::traitToTranslationDomain(CommunicationTrait::class)] + $defaultArray;
        $builder->add(
            'phone',
            TextType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('phone') + ['required' => false]
        );
        $builder->add(
            'email',
            EmailType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('email')
        );
        $builder->add(
            'webpage',
            UrlType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('webpage') + ['required' => false]
        );

        return $builder;
    }

    /**
     * @param CommunicationTrait $source
     */
    public function setCommunicationFieldsFrom($source)
    {
        $this->setPhone($source->getPhone());
        $this->setEmail($source->getEmail());
        $this->setWebpage($source->getWebpage());
    }
}
