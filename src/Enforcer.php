<?php

namespace Donjan\Casbin;

use Casbin\Enforcer as BaseEnforcer;
use Casbin\Persist\Watcher;
use Donjan\Casbin\Watcher\RedisWatcher;
use Psr\Container\ContainerInterface;
use Hyperf\Utils\ApplicationContext;

/**
 * Enforcer
 * @method static bool enforce(...$rvals)
 * @method static array getRolesForUser(string $name, string ...$domain)
 * @method static array getUsersForRole(string $name, string ...$domain)
 * @method static bool hasRoleForUser(string $name, string $role, string ...$domain)
 * @method static bool addRoleForUser(string $user, string $role, string ...$domain)
 * @method static bool deleteRoleForUser(string $user, string $role, string ...$domain)
 * @method static bool deleteRolesForUser(string $user, string ...$domain)
 * @method static bool deleteUser(string $user)
 * @method static bool deleteRole(string $role)
 * @method static bool deletePermission(string ...$permission)
 * @method static bool addPermissionForUser(string $user, string ...$permission)
 * @method static bool deletePermissionForUser(string $user, string ...$permission)
 * @method static bool deletePermissionsForUser(string $user)
 * @method static array getPermissionsForUser(string $user)
 * @method static bool hasPermissionForUser(string $user, string ...$permission)
 * @method static array getImplicitRolesForUser(string $name, string ...$domain)
 * @method static array getImplicitPermissionsForUser(string $user, string ...$domain)
 * @method static array getImplicitUsersForPermission(string ...$permission)
 * @method static array getUsersForRoleInDomain(string $name, string $domain)
 * @method static array getRolesForUserInDomain(string $name, string $domain)
 * @method static array getPermissionsForUserInDomain(string $name, string $domain)
 * @method static bool addRoleForUserInDomain(string $user, string $role, string $domain)
 * @method static bool deleteRoleForUserInDomain(string $user, string $role, string $domain)
 * @method static void setWatcher(Watcher $watcher)
 * @method static void loadPolicy()
 */
class Enforcer
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __call($method, $parameters)
    {
        return $this->container->get(BaseEnforcer::class)->{$method}(...$parameters);
    }

    /**
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return ApplicationContext::getContainer()->get(BaseEnforcer::class)->{$method}(
            ...
            $parameters
        );
    }

    public static function start()
    {
        /** @var RedisWatcher $watcher */
        $watcher = make(RedisWatcher::class);
        static::setWatcher($watcher);
        $watcher->setUpdateCallback(function () {
            static::loadPolicy();

        });
        $watcher->subscribe();
    }

}
