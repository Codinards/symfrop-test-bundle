<?php

namespace Njeaner\Symfrop\Composer;

use Composer\Script\Event;

class InstallationScripts
{
    public static function prePackageInstall(Event $event)
    {
        $configContent = "
symfrop_bundle:
    resources_info:
        App/Controller:  '%kernel.project_dir%/src/Controller'
    entities:
        entity:
            user_entity: 'Njeaner\Symfrop\Entity\User'
            role_entity: 'Njeaner\Symfrop\Entity\Role'
            action_entity: 'Njeaner\Symfrop\Entity\Action'
        form: 
            role_form: 'Njeaner\Symfrop\Form\RoleType'
            action_form: 'Njeaner\Symfrop\Form\ActionType'
            user_role_form: 'Njeaner\Symfrop\Form\UserRoleType'

    app_roles:
        ROLE_USER: 'user'
        ROLE_ADMIN: 'admin'
        ROLE_SUPERADMIN: ['root', false]
    templates: 
        navbar_top: null
        navbar_bottom: null
        scripts: []
        stylesheets: []
";
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $configDir =  dirname($vendorDir) . '/config';
        $packageDir = $configDir . '/packages';
        if (file_exists($packageDir)) {
            file_put_contents($configDir . '/symfrop.yaml', $configContent);
        }
        $routesDir = $configDir . '/routes';
        if (file_exists($routesDir)) {
            $routeConfig = "
controllers:
    resource: '@SymfropBundle/Controller/'
    type: attribute";

            file_put_contents($routesDir . '/attributes.yaml', $routeConfig);
        }
    }
}
