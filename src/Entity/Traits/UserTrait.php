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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait UserTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $passwordHash;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $resetHash;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $registrationDate;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $agbAccepted = false;

    /**
     * @var string
     */
    private $plainPassword;
    private $repeatPlainPassword;

    /**
     * @param FormBuilderInterface $builder
     * @param array $defaultArray
     * @param bool $agb
     *
     * @return FormBuilderInterface
     */
    public static function getRegisterUserBuilder(FormBuilderInterface $builder, $defaultArray = [], $agb = true)
    {
        $builderArray = ['translation_domain' => NamingHelper::traitToTranslationDomain(UserTrait::class)] + $defaultArray;
        static::getEmailBuilder($builder, $builderArray);
        static::getPlainPasswordBuilder($builder, $builderArray);
        if ($agb) {
            $builder->add(
                'agbAccepted',
                CheckboxType::class,
                $builderArray + NamingHelper::propertyToTranslationForBuilder('agbAccepted')
            );
        }

        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $builderArray
     */
    private static function getEmailBuilder(FormBuilderInterface $builder, $builderArray)
    {
        $builder->add(
            'email',
            EmailType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('email')
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $builderArray
     */
    private static function getPlainPasswordBuilder(FormBuilderInterface $builder, $builderArray)
    {
        $builder->add(
            'plainPassword',
            PasswordType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('plainPassword')
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $defaultArray
     *
     * @return FormBuilderInterface
     */
    public static function getLoginBuilder(FormBuilderInterface $builder, $defaultArray = [])
    {
        $builderArray = ['translation_domain' => NamingHelper::traitToTranslationDomain(UserTrait::class)] + $defaultArray;
        static::getEmailBuilder($builder, $builderArray);
        static::getPlainPasswordBuilder($builder, $builderArray);

        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     *
     * @return FormBuilderInterface
     */
    public static function getResetUserBuilder(FormBuilderInterface $builder, $defaultArray = [])
    {
        $builderArray = ['translation_domain' => NamingHelper::traitToTranslationDomain(UserTrait::class)] + $defaultArray;
        static::getEmailBuilder($builder, $builderArray);

        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param $defaultArray
     *
     * @return FormBuilderInterface
     */
    public static function getSetPasswordBuilder(FormBuilderInterface $builder, $defaultArray = [])
    {
        $builderArray = ['translation_domain' => NamingHelper::traitToTranslationDomain(UserTrait::class)] + $defaultArray;
        static::getPlainPasswordBuilder($builder, $builderArray);
        $builder->add(
            'repeatPlainPassword',
            PasswordType::class,
            $builderArray + NamingHelper::propertyToTranslationForBuilder('repeatPlainPassword')
        );

        return $builder;
    }

    /**
     * @param $email
     *
     * sets all fields of the user object
     *
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

    /**
     * hashes the password if valid, and erases credentials.
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
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     *
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
    public function getResetHash()
    {
        return $this->resetHash;
    }

    /**
     * @param string $resetHash
     *
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
     * @return string
     */
    public function getRepeatPlainPassword()
    {
        return $this->repeatPlainPassword;
    }

    /**
     * @param string $plainPassword
     *
     * @return static
     */
    public function setRepeatPlainPassword($plainPassword)
    {
        $this->repeatPlainPassword = $plainPassword;

        return $this;
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
     * @return bool
     */
    public function isValidPlainPassword()
    {
        return mb_strlen($this->getPlainPassword()) >= 8;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     *
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
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @param string $passwordHash
     *
     * @return static
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    /**
     * checks if the user is allowed to login.
     *
     * @return bool
     */
    public function canLogin()
    {
        return $this->isActive;
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
     * @ORM\PrePersist()
     */
    public function prePersistsHandler()
    {
        $this->resetHash = uniqid();
    }

    /**
     * get the user identifier.
     *
     * @return string
     */
    protected function getUserIdentifier()
    {
        return $this->email;
    }

    /**
     * check if two users are equal.
     *
     * @param UserTrait $user
     *
     * @return bool
     */
    protected function isEqualToUser($user)
    {
        /* @var UserTrait $user */
        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        if ($this->getPasswordHash() !== $user->getPasswordHash()) {
            return false;
        }

        return true;
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
}
