<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18.04.2017
 * Time: 11:42
 */

namespace AppBundle\Entity\Traits;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

trait UserTrait
{
    /**
     * @var string $email
     *
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @var string $passwordHash
     *
     * @ORM\Column(type="text")
     */
    private $passwordHash;

    /**
     * @var string $resetHash
     *
     * @ORM\Column(type="text")
     */
    private $resetHash;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @var \DateTime $registrationDate
     *
     * @ORM\Column(type="datetime")
     */
    private $registrationDate;

    /**
     * @var string $plainPassword
     */
    private $plainPassword;


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
     * @return string
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param string $isActive
     * @return static
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * @param \DateTime $registrationDate
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;
    }

    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @param string $passwordHash
     * @return static
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    /**
     * @return string
     */
    public function getResetHash()
    {
        return $this->resetHash;
    }

    /**
     * @param string $resetHash
     * @return static
     */
    public function setResetHash($resetHash)
    {
        $this->resetHash = $resetHash;
        return $this;
    }

    /**
     * @param string $plainPassword
     * @return UserTrait
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * hashes the password if valid, and erases credentials
     */
    public function hashAndRemovePlainPassword()
    {
        if ($this->isValidPlainPassword()) {
            $this->setPasswordHash(password_hash($this->getPlainPassword(), PASSWORD_BCRYPT));
        }
        $this->eraseCredentials();
    }

    /**
     * @return bool
     */
    public function tryLoginWithPlainPassword()
    {
        if ($this->isValidPlainPassword()) {
            return password_verify($this->getPlainPassword(), $this->getPasswordHash());
        }
        return false;
    }

    /**
     * checks if the user is allowed to login
     *
     * @return boolean
     */
    public function canLogin()
    {
        return $this->isActive;
    }

    /**
     * @return bool
     */
    public function isValidPlainPassword()
    {
        return $this->getPlainPassword() != "";
    }

    /**
     * get the user identifier
     *
     * @return string
     */
    protected function getUserIdentifier()
    {
        return $this->email;
    }

    /**
     * check if two users are equal
     *
     * @param UserTrait $user
     * @return bool
     */
    protected function isEqualToUser($user)
    {
        /* @var UserTrait $user */
        if ($this->getUsername() != $user->getUsername())
            return false;

        if ($this->getPasswordHash() != $user->getPasswordHash())
            return false;

        return true;
    }


    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->getPasswordHash();
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        $this->setPlainPassword(null);
    }


    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     * @return FormBuilderInterface
     */
    public static function getUserBuilder(FormBuilderInterface $builder, $defaultArray)
    {
        return static::mapUserFields($builder, $defaultArray);
    }

    /**
     * @param FormMapper $formMapper
     * @param $defaultArray
     * @return FormMapper
     */
    public static function getUserFormFields(FormMapper $formMapper, $defaultArray)
    {
        $mapper = static::mapUserFields($formMapper, $defaultArray);
        $mapper->add("isEnabled", CheckboxType::class);
        return $mapper;
    }

    /**
     * @param FormBuilderInterface|FormMapper $mapper
     * @param $defaultArray
     * @return FormBuilderInterface|FormMapper
     */
    private static function mapUserFields($mapper, $defaultArray)
    {
        return $mapper
            ->add("email", EmailType::class, $defaultArray)
            ->add("plainPassword", TextType::class, $defaultArray + ["required" => false]);
    }

    /**
     * @param ListMapper $listMapper
     * @return ListMapper
     */
    public static function getUserListFields(ListMapper $listMapper)
    {
        return $listMapper->addIdentifier("email")->add("registrationDate")->add("isEnabled");
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersistsHandler()
    {
        $this->resetHash = uniqid();
    }
}