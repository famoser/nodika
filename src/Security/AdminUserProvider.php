<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18.04.2017
 * Time: 11:34
 */

namespace App\Security;


use App\Entity\AdminUser;
use App\Security\Base\BaseUserProvider;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminUserProvider extends BaseUserProvider
{
    /**
     * @var RegistryInterface $registry
     */
    private $registry;

    /**
     * @var string[] $adminEmails
     */
    private $adminEmails;

    /**
     * AdminUserProvider constructor.
     * @param RegistryInterface $registry
     * @param $adminEmails
     */
    public function __construct(RegistryInterface $registry, $adminEmails)
    {
        $this->registry = $registry;
        $this->adminEmails = explode(",", $adminEmails);
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
        $user = $this->registry->getRepository("App:AdminUser")->findOneBy(["email" => $username]);
        if ($user != null) {
            if (in_array($user->getEmail(), $this->adminEmails)) {
                $user->addRole("ROLE_ADMIN");
            }
            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist in CustomerUserProvider.', $username)
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
        if (!$user instanceof AdminUser) {
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
        return AdminUser::class === $class;
    }
}