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

use Doctrine\ORM\Mapping as ORM;
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
    private $isEnabled = true;

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
     * @Assert\IsTrue()
     */
    private $agbAccepted = false;

    /**
     * @var string
     */
    private $plainPassword;
    private $repeatPlainPassword;

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
     * @param boolean $isEnabled
     *
     * @return static
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

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
     * checks if the user is allowed to login.
     *
     * @return bool
     */
    public function canLogin()
    {
        return $this->isEnabled;
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
        return $this->passwordHash;
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
        return $this->isEnabled;
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

        if ($this->getPassword() !== $user->getPassword()) {
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

    /**
     * hashes the plainPassword and erases credentials.
     */
    public function setPassword()
    {
        $this->passwordHash = password_hash($this->getPlainPassword(), PASSWORD_BCRYPT);
        $this->setPlainPassword(null);
        $this->setRepeatPlainPassword(null);
    }

    /**
     * creates a new reset hash
     */
    public function setResetHash()
    {
        $newHash = '';
        //0-9, A-Z, a-z
        $allowedRanges = [[48, 57], [65, 90], [97, 122]];
        for ($i = 0; $i < 20; ++$i) {
            $rand = mt_rand(20, 160);
            $allowed = false;
            for ($j = 0; $j < count($allowedRanges); ++$j) {
                if ($allowedRanges[$j][0] <= $rand && $allowedRanges[$j][1] >= $rand) {
                    $allowed = true;
                }
            }
            if ($allowed) {
                $newHash .= chr($rand);
            } else {
                --$i;
            }
        }

        $this->resetHash = $newHash;
    }
}
