<?php

namespace Njeaner\Symfrop\DependencyInjection;

use Njeaner\Symfrop\Entity\Role;
use Njeaner\Symfrop\Entity\User;
use Njeaner\Symfrop\Entity\Action;
use Njeaner\Symfrop\Form\RoleType;
use Njeaner\Symfrop\Form\ActionType;
use Njeaner\Symfrop\Form\UserRoleType;
use Njeaner\Symfrop\Core\Service\CONSTANTS;
use Njeaner\Symfrop\Entity\Contract\RoleInterface;
use Njeaner\Symfrop\Entity\Contract\UserInterface;
use Njeaner\Symfrop\Entity\Contract\ActionInterface;
use Njeaner\Symfrop\Exceptions\NotFoundClassException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Njeaner\Symfrop\Exceptions\InvalidConfigurationTypeException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfrop_bundle');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->variableNode('resources_info')->isRequired()->defaultValue([
                "App\Controller" => "%kernel.project_dir%/src/Controller"
            ])
            ->validate()->always(function ($value) {
                $this->validateOptionType('symfrop_bundle.resources_info', $value, 'array');
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        $optionKeys = array_keys($v);
                        array_map(function ($item) use ($k) {
                            if (!in_array($item, [0, 1, 'except', 'path'])) {
                                throw new InvalidConfigurationTypeException(
                                    'symfrop_bundle.resources_info. "' . $k . '". Invalid options key "' . $item . '" option. (symfrop_bundle)'
                                );
                            }
                        }, $optionKeys);
                        $path = $v['path'] ?? $v[0] ?? null;
                        $except = $v['except'] ?? $v[1] ?? null;
                        if (count($v) !== 2) {
                            throw new InvalidConfigurationTypeException(
                                'symfrop_bundle.resources_info. "' . $k . '", Invalid values given. (symfrop_bundle)'
                            );
                        }
                        if ($path === null) {
                            throw new InvalidConfigurationTypeException(
                                'symfrop_bundle.resources_info. "' . $k . '", Missing "path" option. (symfrop_bundle)'
                            );
                        }
                        if ($except === null) {
                            throw new InvalidConfigurationTypeException(
                                'symfrop_bundle.resources_info. "' . $k . '", Missing "except" option. (symfrop_bundle)'
                            );
                        }
                        $this->validateFile('symfrop_bundle.resources_info "' . $k . '"', $path, 'file or Directory');
                    } else {
                        $this->validateFile('symfrop_bundle.resources_info "' . $k . '"', $v, 'file or Directory');
                    }
                }
                return $value;
            })->end()
            ->end()

            ->variableNode('app_roles')->isRequired()->defaultValue(CONSTANTS::APP_ROLES_ACTION)->validate()->always(function ($v) {

                $this->validateOptionType('symfrop_bundle.app_roles', $v, 'array');
                if (empty($v)) {
                    throw new InvalidConfigurationTypeException(
                        'symfrop_bundle.app_roles. Empty array can not be given. expected values Example: {ROLE_USER :"user"} (symfrop_bundle)'
                    );
                }
                foreach ($v as $opt) {
                    if (
                        (!is_array($opt) and !is_string($opt))
                        or (is_array($opt) and (count($opt) !== 2 or !is_string($opt[0]) or !is_bool($opt[1])))
                    ) {
                        throw new InvalidConfigurationTypeException(
                            'symfrop_bundle.app_roles. Invalid argument given (symfrop_bundle)'
                        );
                    }
                }
                return $v;
            })->end()->end()
            /************************************************************ */
            ->arrayNode('templates')
            ->children()
            ->scalarNode('navbar_top')->defaultNull()->end()
            ->scalarNode('navbar_bottom')->defaultNull()->end()
            ->variableNode('stylesheets')->defaultValue(null)->validate()->always(
                function ($v) {
                    return $v;
                    $this->validateOptionType('symfrop_bundle.templates.stylesheets', $v, 'array');
                    foreach ($v as $elt) {
                        $this->validateOptionType('symfrop_bundle.templates.stylesheets values', $elt, 'string');
                    }
                    return $v;
                }
            )->end()->end()
            ->variableNode('scripts')->defaultValue(null)->validate()->always(
                function ($v) {
                    $this->validateOptionType('symfrop_bundle.templates.scripts', $v, 'array');
                    foreach ($v as $elt) {
                        $this->validateOptionType('symfrop_bundle.templates.scripts values', $elt, 'string');
                    }
                    return $v;
                }
            )->end()->end()
            ->end()
            ->end()

            /******************************************************************** */
            ->arrayNode('entities')->isRequired()
            ->children()
            ->arrayNode('entity')->isRequired()
            ->children()
            ->scalarNode('user_entity')->isRequired()->cannotBeEmpty()->defaultValue(User::class)->validate()->always()->then(
                fn ($v) => $this->ValidateEntityName('symfrop_bundle.entities.entity.user_entity', $v, 'string', UserInterface::class)
            )->end()->end()
            ->scalarNode('role_entity')->isRequired()->cannotBeEmpty()->defaultValue(Role::class)->validate()->always()->then(
                fn ($v) => $this->ValidateEntityName('symfrop_bundle.entities.entity.role_entity', $v, 'string', RoleInterface::class)
            )->end()->end()
            ->scalarNode('action_entity')->isRequired()->cannotBeEmpty()->defaultValue(Action::class)->validate()->always()->then(
                fn ($v) => $this->ValidateEntityName('symfrop_bundle.entities.entity.role_entity', $v, 'string', ActionInterface::class)
            )->end()->end()
            ->end()
            ->end()
            /****************************************************** */
            ->arrayNode('form')->isRequired()
            ->children()
            ->scalarNode('user_role_form')->isRequired()->cannotBeEmpty()->defaultValue(UserRoleType::class)->validate()->always()->then(
                fn ($v) => $this->controlIfClassExists('symfrop_bundle.entities.forms.user_role_form', $v)
            )->end()->end()
            ->scalarNode('role_form')->isRequired()->cannotBeEmpty()->defaultValue(RoleType::class)->validate()->always()->then(
                fn ($v) => $this->controlIfClassExists('symfrop_bundle.entities.forms.role_form', $v)
            )->end()->end()
            ->scalarNode('action_form')->isRequired()->cannotBeEmpty()->defaultValue(ActionType::class)->validate()->always()->then(
                fn ($v) => $this->controlIfClassExists('symfrop_bundle.entities.forms.role_form', $v)
            )->end()->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }

    private function validateFiles(string $key, $v, $type = 'file')
    {
        $this->validateOptionType($key, $v, 'array');
        foreach ($v as $file_name) {
            $file_name = $type === 'file' ? $file_name . '.php' : $file_name;
            $this->validateFile($key, $file_name, $type);
        }
    }

    private function validateFile(string $key, $v, string $type = 'file')
    {
        $this->validateOptionType($key, $v, 'string');
        if (!file_exists($v)) {
            throw new NotFoundClassException('Missing ' . $type . ' "' . $v . '" passed in "' . $key . '" configuration option. (symfrop_bundle)');
        }
        return $v;
    }

    private function validateOptionType(string $key,  $v, string $type)
    {
        $function = 'is_' . $type;
        if (!$function($v)) {
            throw new InvalidConfigurationTypeException("Configuration option \"$key\" must be from type \"$type\". (symfrop_bundle)");
        }
        return $v;
    }

    private function controlIfClassExists($key, $v)
    {
        if (!class_exists($v)) {
            throw new NotFoundClassException("Class \"$v\" does not exists in \"$key\" configuration option. (symfrop_bundle)");
        }
        return $v;
    }

    private function instanceOf(string $key, $v, string $type)
    {
        if (!$v instanceof $type) {
            throw new InvalidConfigurationTypeException(
                "Configuration option \"$key\" must be instance of \"$type\". (symfrop_bundle)"
            );
        }
        return $v;
    }

    private function ValidateEntityName(string $key, $v, string $type)
    {
        $this->validateOptionType($key, $v, $type);
        $this->controlIfClassExists($key, $v);

        return $v;
    }
}
