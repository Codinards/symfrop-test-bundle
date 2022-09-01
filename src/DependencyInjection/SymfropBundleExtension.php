<?php

namespace Njeaner\Symfrop\DependencyInjection;

use Error;
use Exception;
use Njeaner\Symfrop\SymfropBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 0.0.1
 */
class SymfropBundleExtension extends Extension
{
    static private SymfropBundle $BUNDLE;

    private array $configs;

    public function __construct(SymfropBundle $symfropBundle)
    {
        self::$BUNDLE = $symfropBundle;
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../config'));
        $loader->load('services.php');
        $configuration = $this->getConfiguration($configs, $container);
        $this->configs = $this->processConfiguration($configuration, $configs);
        file_put_contents(dirname(__DIR__) . '/Resources/cache/configs.json', json_encode($this->configs));
    }

    /**
     * Get the value of instance
     */
    public static function getInstance(): self
    {
        return self::$BUNDLE->getExtension();
    }

    /**
     * Get the value of configs
     */
    public function getConfigs(): array
    {
        try {
            return $this->configs;
        } catch (Exception | Error) {
            return $this->configs = json_decode(file_get_contents(dirname(__DIR__) . '/Resources/cache/configs.json'), true);
        }
    }
}
