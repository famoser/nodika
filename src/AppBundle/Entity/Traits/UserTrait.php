<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18.04.2017
 * Time: 11:42
 */

namespace AppBundle\Entity\Traits;

use AppBundle\Helper\HashHelper;
use AppBundle\Helper\NamingHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait UserTrait
{
    /**
     * @var string $email
     *
     * @ORM\Column(type="text", unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
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
     * @var bool $agbAccepted
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $agbAccepted = false;

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
     * @return bool
     */
    public function isAgbAccepted()
    {
        return $this->agbAccepted;
    }

    /**
     * @param bool $agbAccepted
     */
    public function setAgbAccepted($agbAccepted)
    {
        $this->agbAccepted = $agbAccepted;
    }

    /**
     * @param string $plainPassword
     * @return static
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

    private $repeatPlainPassword;

    /**
     * @param string $plainPassword
     * @return static
     */
    public function setRepeatPlainPassword($plainPassword)
    {
        $this->repeatPlainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepeatPlainPassword()
    {
        return $this->repeatPlainPassword;
    }

    /**
     * hashes the password if valid, and erases credentials
     */
    public function persistNewPassword()
    {
        $this->setPasswordHash(password_hash($this->getPlainPassword(), PASSWORD_BCRYPT));
        $this->eraseCredentials();
        $this->setResetHash(HashHelper::createNewResetHash());
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
        $this->setRepeatPlainPassword(null);
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
        return $this->getPlainPassword() != "" && strlen($this->getPlainPassword()) >= 8;
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
    public static function getRegisterUserBuilder(FormBuilderInterface $builder, $defaultArray = [])
    {
        $builderArray = ["translation_domain" => NamingHelper::traitToTranslationDomain(UserTrait::class)] + $defaultArray;
        static::getEmailBuilder($builder, $builderArray);
        static::getPlainPasswordBuilder($builder, $builderArray);
        $builder->add(
            "agbAccepted",
            CheckboxType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("agbAccepted")
        );
        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $defaultArray
     * @return FormBuilderInterface
     */
    public static function getLoginBuilder(FormBuilderInterface $builder, $defaultArray = [])
    {
        $builderArray = ["translation_domain" => NamingHelper::traitToTranslationDomain(UserTrait::class)] + $defaultArray;
        static::getEmailBuilder($builder, $builderArray);
        static::getPlainPasswordBuilder($builder, $builderArray);
        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     * @return FormBuilderInterface
     */
    public static function getResetUserBuilder(FormBuilderInterface $builder, $defaultArray = [])
    {
        $builderArray = ["translation_domain" => NamingHelper::traitToTranslationDomain(UserTrait::class)] + $defaultArray;
        static::getEmailBuilder($builder, $builderArray);
        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     * @return FormBuilderInterface
     */
    public static function getSetPasswordBuilder(FormBuilderInterface $builder, $defaultArray = [])
    {
        $builderArray = ["translation_domain" => NamingHelper::traitToTranslationDomain(UserTrait::class)] + $defaultArray;
        static::getPlainPasswordBuilder($builder, $builderArray);
        $builder->add(
            "repeatPlainPassword",
            PasswordType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("repeatPlainPassword")
        );
        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $builderArray
     */
    private static function getPlainPasswordBuilder(FormBuilderInterface $builder, $builderArray)
    {
        $builder->add(
            "plainPassword",
            PasswordType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("plainPassword")
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $builderArray
     */
    private static function getEmailBuilder(FormBuilderInterface $builder, $builderArray)
    {
        $builder->add(
            "email",
            EmailType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder("email")
        );
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersistsHandler()
    {
        $this->resetHash = uniqid();
    }

    /**
     * @param $email
     *
     * sets all fields of the user object
     * @return static
     */
    private static function createUserFromEmail($email)
    {
        $object = new static();
        $object->setRegistrationDate(new \DateTime());
        $object->setIsActive(true);
        $object->setEmail($email);
        $object->setPlainPassword(uniqid());
        $object->persistNewPassword();
        return $object;
    }
}