<?php

namespace Njeaner\Symfrop\Core\Service;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
enum CONSTANTS
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';

    const ROLE_USER_TITLE = 'user';
    const ROLE_ADMIN_TITLE = 'admin';
    const ROLE_SUPERADMIN_TITLE = 'root';

    const APP_ROLES = [];

    const APP_ROLES_ACTION = [
        self::ROLE_USER => self::ROLE_USER_TITLE,
        self::ROLE_ADMIN => self::ROLE_ADMIN_TITLE,
        self::ROLE_SUPERADMIN => [self::ROLE_SUPERADMIN_TITLE, false]
    ];

    const ROLE_ALL_ADMINS = [
        self::ROLE_ADMIN,
        self::ROLE_SUPERADMIN
    ];

    const ROLE_ALL = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
        self::ROLE_SUPERADMIN
    ];

    const AUTH_FORMS = [
        'role_form' => 'Njeaner\Symfrop\Form\RoleType',
        'action_form' => 'Njeaner\Symfrop\Form\ActionType',
        'user_role_form' => 'Njeaner\Symfrop\Form\UserRoleType'
    ];

    const AUTH_ENTITY = [
        'user_entity' => 'Njeaner\Symfrop\Entity\User',
        'role_entity' => 'Njeaner\Symfrop\Entity\Role',
        'action_entity' => 'Njeaner\Symfrop\Entity\Action'
    ];
}
