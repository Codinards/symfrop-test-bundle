<?php

namespace Njeaner\Symfrop\Core\Manager;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Error;
use Exception;
use Njeaner\Symfrop\Controller\SymfropController;
use Njeaner\Symfrop\Core\Annotation\RouteActionInterface;
use Njeaner\Symfrop\Core\Manager\Exceptions\AnnotationReaderException;
use Njeaner\Symfrop\Core\Service\Config;
use Njeaner\Symfrop\Core\Service\CONSTANTS;
use Njeaner\Symfrop\DependencyInjection\SymfropBundleExtension;
use Njeaner\Symfrop\Entity\Contract\ActionInterface;
use Njeaner\Symfrop\Entity\Contract\RoleInterface;
use Njeaner\Symfrop\Exceptions\SymfropBaseException;
use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class AnnotationReader
{
    /**
     * @var Collection
     */
    private Collection $annotations;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $manager;

    protected ManagerRegistry $doctrine;

    protected ContainerInterface $container;

    private array $configs = [];

    private ?Config $config = null;

    private  array $symfropDefaultAction = [
        CONSTANTS::ROLE_INDEX,
        CONSTANTS::USER_INDEX,
        CONSTANTS::ROLE_CREATE,
        CONSTANTS::ROLE_UPDATE,
        CONSTANTS::ROLE_DELETE,
        CONSTANTS::USER_ROLE_EDIT,
        CONSTANTS::ACTION_EDIT,
    ];

    private array $symfropResolveName = [];

    public function __construct(
        EntityManagerInterface $manager,
        ManagerRegistry $doctrine,
        ContainerInterface $container
    ) {
        $this->manager = $manager;
        $this->annotations = new ArrayCollection([]);
        $this->doctrine = $doctrine;
        $this->container = $container;
    }

    public function getConfigs()
    {
        if (empty($this->configs)) {
            $this->configs = SymfropBundleExtension::getInstance()->getConfigs();
        }
        return $this->configs;
    }

    public function readUserActionAnnotations()
    {
        $resources = $this->getControllerFilenames();

        foreach ($resources as $namespace => $path) {
            if (is_array($path)) {
                dd("This functionnality is not made yet");
            } else {
                if (is_file($path)) {
                    $this->getFileUserActionAnnotation($namespace);
                } else {
                    $this->iterateDirectory($path, $namespace);
                }
            }
        }

        $this->iterateDirectory(dirname(dirname(__DIR__)) . '/Controller', 'Njeaner\Symfrop\Controller');

        return $this->manager->getRepository(
            Config::getInstance()->getActionEntity()
        )->findAll();
    }

    public function iterateDirectory(string $directory, $directoryNamepace)
    {
        $folder = dir($directory);

        while (($file = $folder->read()) !== false) {
            if ($file === "." || $file === "..") continue;

            if (is_dir($subFolder = $directory . DIRECTORY_SEPARATOR . $file)) {
                $this->iterateDirectory($subFolder, $directoryNamepace . '\\' . ucfirst($file));
            } else {
                if (str_contains($file, '.php')) {
                    $classname = str_replace(
                        '.php',
                        '',
                        $directoryNamepace . '\\' . $file
                    );

                    $this->getFileUserActionAnnotation($classname);
                }
            }
        }
    }

    private function resolveEntity(string $entityname, string $instance)
    {
        try {
            $userAction = $this->container->get($entityname);
        } catch (Exception | Error $e) {
            try {
                $userAction = new $entityname();
            } catch (Exception $e) {
                throw new SymfropBaseException($e->getMessage(), 0, $e);
            }
        }

        if (!$userAction instanceof $instance) {
            throw new AnnotationReaderException("$entityname must be an instance of $instance");
        }

        return $userAction;
    }

    private function getFileUserActionAnnotation(string $classname)
    {
        $classname = $this->cleanFilename($classname);

        if (class_exists($classname)) {
            $reflectionClass = new ReflectionClass($classname);
            $classAttributes = $reflectionClass->getAttributes(RouteActionInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            $globalCondition = $globalConditionOption = $globalTarget = null;
            $globalIsUpdated = $globalUpdatedRole = $globalName = null;
            $globalHasAuth = $globalIsUpdatable = true;
            if (!empty($classAttributes)) {
                if (count($classAttributes) > 1) {
                    throw new AnnotationReaderException(
                        'Several instance of "' . RouteActionInterface::class . '" attributes has been detected in "'
                            . $classname . '" attributes ('
                            . join(', ', array_map(fn (\ReflectionAttribute $attr) => $attr->getName(), $classAttributes))
                            . '). Use of more than one attribute of this instance is not permit in the same class attributes'
                    );
                }
                /** @var RouteActionInterface */
                $classAttributes = $classAttributes[0]->newInstance();
                $globalName = $classAttributes->getName();
                $globalCondition = $classAttributes->getActionCondition();
                $globalIsUpdatable = $classAttributes->getIsUpdatable();
                $globalIsUpdated = $classAttributes->getIsUpdated();
                $globalHasAuth = $classAttributes->getHasAuth();
                $globalUpdatedRole = $classAttributes->getUpdatedRole();
                $globalConditionOption = $classAttributes->getConditionOption();
                $globalTarget = $classAttributes->getTarget();
            }

            $methods = $reflectionClass->getMethods();
            foreach ($methods as $method) {
                $attributes = $method->getAttributes(RouteActionInterface::class, ReflectionAttribute::IS_INSTANCEOF);
                if (count($attributes) > 1) {
                    throw new AnnotationReaderException(
                        'Several instance of "' . RouteActionInterface::class . '" attributes has been detected in "'
                            . $classname . '::' . $method->getName() . '" method attributes ('
                            . join(', ', array_map(fn (\ReflectionAttribute $attr) => $attr->getName(), $attributes))
                            . '). Use of more than one attribute of this instance is not permit in the same class method attributes'
                    );
                }
                foreach ($attributes as $attribute) {
                    try {
                        /** @var RouteActionInterface $annotationUserAction */
                        $annotationUserAction = $attribute->newInstance();
                    } catch (\Exception $e) {
                        throw new AnnotationReaderException(
                            $e->getMessage() . '. [' . $attribute->getName() . ' annotation used in ' . $classname . "::" . $method->getName() . ' method ]'
                        );
                    }
                    $userActionName = ($globalName ?? '') . $annotationUserAction->getName();
                    if ($classname === SymfropController::class and !in_array($userActionName, $this->symfropResolveName)) {
                        if (null === ($userAction = $this->manager->getRepository(
                            Config::getInstance()->getActionEntity()
                        )->findOneBy(['name' => $userActionName]))) {
                            /** @var ActionInterface $userAction */
                            $userAction = $this->resolveEntity(
                                Config::getInstance()->getActionEntity(),
                                ActionInterface::class
                            );
                            $userAction->setName($userActionName);
                            $userAction->setTitle(
                                $annotationUserAction->getTitle() ?? $userAction->getName()
                            );
                            $userAction->setIsUpdatable($globalIsUpdatable == false ? $globalIsUpdatable : $annotationUserAction->getIsUpdatable());
                            $userAction->setHasAuth($globalHasAuth == false ? $globalHasAuth : $annotationUserAction->getHasAuth());
                            $userAction->setIsIndex($annotationUserAction->getIsIndex());
                            $userAction->setCondition($annotationUserAction->getActionCondition() ?? $globalCondition);
                            $userAction->setConditionOption($annotationUserAction->getConditionOption() ?? $globalConditionOption);
                            $roles = $this->manager->getRepository(
                                Config::getInstance()->getRoleEntity()
                            )->findAll();

                            if (empty($roles)) {
                                throw new AnnotationReaderException(
                                    "Any " . Config::getInstance()->getRoleEntity() . ' find in database.'
                                );
                            }
                            $targets = $annotationUserAction->getTarget();
                            if (empty($target)) {
                                if (!empty($globalTarget)) {
                                    $target = $globalTarget;
                                } else {
                                    $target = array_keys(Config::getInstance()->getRoles());
                                }
                            }
                            /** @var RoleInterface $role */
                            foreach ($roles as $role) {
                                foreach ($targets as $target) {
                                    if ($target === $role->getName()) {
                                        $role->addAction($userAction);
                                    }
                                }
                                $this->manager->persist($userAction);
                            }
                        } else {
                            if ($annotationUserAction->getIsUpdated() === true || $globalIsUpdated === true) {
                                /** @var ActionInterface */
                                $userAction = $annotationUserAction->updateAction($userAction);
                            }
                            if ($annotationUserAction->getUpdatedRole() || $globalUpdatedRole === true) {
                                $targets = $annotationUserAction->getTarget();
                                if (empty($target)) {
                                    if (!empty($globalTarget)) {
                                        $target = $globalTarget;
                                    } else {
                                        $target = array_keys(Config::getInstance()->getRoles());
                                    }
                                }
                                $rolesNames = [];
                                $roles = $this->manager->getRepository(
                                    Config::getInstance()->getRoleEntity()
                                )->createQueryBuilder('__roles__')->where(':action MEMBER OF __roles__.actions')
                                    ->setParameter('action', $userAction)
                                    ->getQuery()->getResult();
                                /** @var RoleInterface */
                                foreach ($roles as $role) {
                                    if (!in_array($role->getName(), $targets)) {
                                        $rolesNames[] = $role->getName();
                                        $role->removeAction($userAction);
                                    }
                                }

                                foreach ($rolesNames as $name) {
                                    if (in_array($name, $targets)) {
                                        unset($targets[array_search($name, $targets)]);
                                    }
                                }
                                if (!empty($targets)) {
                                    /** @var ServiceEntityRepository */
                                    $repo = $this->manager->getRepository(
                                        Config::getInstance()->getRoleEntity()
                                    );
                                    $roles = $repo->createQueryBuilder('__roles__')
                                        ->where('__roles__.name IN (' . implode(', ', array_map(fn ($item) => ':_' . $item, array_keys($targets))) . ')')
                                        ->setParameters(array_combine(array_map(fn ($item) => (string)('_' . $item), array_keys($targets)), $targets))
                                        ->getQuery()
                                        ->getResult();

                                    /** @var RoleInterface */
                                    foreach ($roles as $role) {
                                        $role->addAction($userAction);
                                    }
                                }
                            }
                            if (in_array($userActionName, $this->symfropDefaultAction)) {
                                $this->symfropResolveName[$userActionName] = $userActionName;
                            }
                        }
                    }
                }
            }
            $this->manager->flush();
        } else {
            throw new AnnotationReaderException(
                'The class "' . $classname . '" does not exists.(look symfrop config file)'
            );
        }
    }

    public function cleanFilename(string $filename, bool $is_namespace = false): string
    {
        $os = getenv()['OS'];
        $sep = (str_contains($os, 'Windows') or $is_namespace) ? '\\' : '/';
        $filename = implode($sep, explode(DIRECTORY_SEPARATOR, $filename));
        $filename = implode($sep, explode('/', $filename));
        return $filename;
    }

    public function getControllerFilenames(): array
    {
        return ($this->config ?? Config::getInstance())->getResources();
    }

    public function iterateData(array $data)
    {
        foreach ($data as $namespace => $dir) {
            yield ['namespace' => $namespace, 'directory' => $dir[0], 'type' => $dir[1]];
        }
    }

    /**
     * Get the value of manager
     *
     * @return  EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->manager;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Get the value of config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Set the value of config
     *
     * @return  self
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }
}
