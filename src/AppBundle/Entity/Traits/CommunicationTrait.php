<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18.04.2017
 * Time: 11:46
 */

namespace AppBundle\Entity\Traits;


use Doctrine\ORM\Mapping as ORM;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

trait CommunicationTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $webpage;

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
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
     * @return static
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * get non-empty communication lines
     *
     * @return string[]
     */
    public function getCommunicationLines()
    {
        $res = [];
        if ($this->getPhone() != "")
            $res[] = $this->getPhone();
        if ($this->getEmail() != "")
            $res[] = $this->getEmail();
        return $res;
    }

    /**
     * get the communication identifier
     *
     * @return string
     */
    protected function getCommunicationIdentifier()
    {
        return implode(",", $this->getCommunicationLines());
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     * @return FormBuilderInterface
     */
    public static function getCommunicationBuilder(FormBuilderInterface $builder, $defaultArray)
    {
        return static::mapCommunicationFields($builder, $defaultArray);
    }

    /**
     * @param FormBuilderInterface|FormMapper $mapper
     * @param $defaultArray
     * @return FormBuilderInterface|FormMapper
     */
    private static function mapCommunicationFields($mapper, $defaultArray)
    {
        return $mapper
            ->add("phone", TextType::class, $defaultArray + ["required" => false])
            ->add("email", EmailType::class, $defaultArray)
            ->add("webpage", TextType::class, $defaultArray + ["required" => false]);
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