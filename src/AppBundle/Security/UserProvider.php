<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18.04.2017
 * Time: 11:34
 */

namespace AppBundle\Security;


use AppBundle\Entity\BusinessUser;
use AppBundle\Entity\User;
use AppBundle\Security\Base\BaseUserProvider;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider extends BaseUserProvider
{
    /**
     * @var RegistryInterface $registry
     */
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $user = $this->registry->getRepository("AppBundle:User")->findOneBy(["email" => $username]);
        dump($user);
        dump($username);
        if ($user != null) {
            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist in UserProvider.', $username)
        );
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return User::class === $class;
    }
}