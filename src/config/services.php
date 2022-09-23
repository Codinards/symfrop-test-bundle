<?php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Njeaner\Symfrop\Command\CrudCommand;
use Njeaner\Symfrop\Command\EntityCommand;
use Njeaner\Symfrop\Command\Utils\DoctrineHelper;
use Njeaner\Symfrop\Command\Utils\FormTypeRenderer;
use Njeaner\Symfrop\Controller\SymfropController;
use Njeaner\Symfrop\Core\Manager\AnnotationManager;
use Njeaner\Symfrop\Core\Manager\AnnotationReader;
use Njeaner\Symfrop\Events\ControllerSubscriber;
use Njeaner\Symfrop\Events\EventSubscriber;
use Njeaner\Symfrop\Events\RequestListener;
use Njeaner\Symfrop\Loader\RouteLoader;
use Njeaner\Symfrop\Permissions\ActionPermissions;
use Njeaner\Symfrop\Twig\SymfropTwigExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $env = $_ENV["APP_ENV"] ?? null;
    $services
        ->set('njeaner_symfrop.njeaner_role_controller', SymfropController::class)
        ->arg('$formFactory', service('form.factory'))
        ->arg('$manager', service(EntityManagerInterface::class))
        ->arg('$requestStack', service(RequestStack::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$security', service(TokenStorageInterface::class))
        ->tag('controller.service_arguments')
        ->call('setContainer', [service('service_container')])
        ->alias(SymfropController::class, 'njeaner_symfrop.njeaner_role_controller')
        ->public();

    $services
        ->set('njeaner_symfrop.annotation_reader', AnnotationReader::class)
        ->arg('$manager', service('doctrine.orm.default_entity_manager'))
        ->arg('$doctrine', service('doctrine'))
        ->arg('$container', service('service_container'))
        ->alias(AnnotationReader::class, 'njeaner_symfrop.annotation_reader')
        ->public();

    $services
        ->set('njeaner_symfrop.annotation_manager', AnnotationManager::class)
        ->arg('$annotationReader', service('njeaner_symfrop.annotation_reader'))
        ->arg('$kernel', service('kernel'))
        ->arg('$security', service('security.helper'))
        ->arg('$requestStack', service(RequestStack::class))
        ->alias(AnnotationManager::class, 'njeaner_symfrop.annotation_manager')
        ->public();

    $services
        ->set(EventSubscriber::class)
        ->arg('$container', service('service_container'))
        ->tag('kernel.event_subscriber')
        ->public();

    $services
        ->set(RequestListener::class)
        ->arg('$annotationManager', service('njeaner_symfrop.annotation_manager'))
        ->tag('kernel.event_listener', ['event' => 'kernel.request']);

    $services
        ->set(ControllerSubscriber::class)
        ->arg('$container', service('service_container'))
        ->tag('kernel.event_listener', ['event' => 'kernel.controller']);

    $services
        ->set(SymfropTwigExtension::class, SymfropTwigExtension::class)
        ->arg('$requestStack', service(RequestStack::class))
        ->arg('$router', service(RouterInterface::class))
        ->arg('$translator', service(TranslatorInterface::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$annotationManager', service('njeaner_symfrop.annotation_manager'))
        ->arg('$tokenManager', service(CsrfTokenManagerInterface::class))
        ->tag('twig.extension')
        ->public();
    if ($env !== 'test') {
        $services
            ->set(DoctrineHelper::class)
            ->arg('$entityNamespace', 'App\Entity')
            ->arg('$registry', service(ManagerRegistry::class));

        $services
            ->set(FormTypeRenderer::class)
            ->arg('$generator', service('maker.generator'));

        $services
            ->set(CrudCommand::class)
            ->arg('$doctrineHelper', service(DoctrineHelper::class))
            ->arg('$formTypeRenderer', service(FormTypeRenderer::class))
            ->tag('console.command')
            ->public();

        $services
            ->set(EntityCommand::class)
            ->arg('$kernel', service(KernelInterface::class))
            ->tag('console.command')
            ->public();
    }
    $services
        ->set(ActionPermissions::class)
        ->arg('$manager', service(EntityManagerInterface::class))
        ->public();

    $services(RouteLoader::class)->tag('routing.loader');
};
