<?php

namespace Njeaner\Symfrop\Permissions;

use Doctrine\ORM\EntityManagerInterface;
use Njeaner\Symfrop\Core\Service\Config;

/**
 * Symfrop controller action permission service
 *
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 0.0.1
 */
class ActionPermissions
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    public function __invoke()
    {
        return count($this->manager->getRepository(
            Config::getInstance()->getUserEntity()
        )->findAll()) <= 1;
    }

    public function createFirstAdmin(): bool
    {
        return count($this->manager->getRepository(
            Config::getInstance()->getUserEntity()
        )->findAll()) === 0;
    }

    public function ifOnlyExistsTheFirstAdmin(): bool
    {
        return count($this->manager->getRepository(
            Config::getInstance()->getUserEntity()
        )->findAll()) === 1;
    }
}
