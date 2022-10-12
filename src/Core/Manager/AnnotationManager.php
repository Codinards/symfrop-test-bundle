<?php

namespace Njeaner\Symfrop\Core\Manager;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Njeaner\Symfrop\Core\Service\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Njeaner\Symfrop\Core\Annotation\RouteAction;
use Symfony\Component\HttpKernel\KernelInterface;
use Njeaner\Symfrop\Entity\Contract\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Njeaner\Symfrop\Entity\Contract\ActionInterface;
use Njeaner\Symfrop\Exceptions\SymfropBaseException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface as UserUserInterface;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class AnnotationManager
{
    protected EntityManagerInterface $manager;

    protected Config $config;

    protected ?UserUserInterface $auth = null;

    private array $resolvedConditions = [];

    /** @var ActionInterface[] */
    private array $userActions = [];


    public function __construct(
        protected AnnotationReader $annotationReader,
        protected KernelInterface $kernel,
        protected Security $security,
        protected RequestStack $requestStack
    ) {
        $this->manager = $annotationReader->getEntityManager();
        $this->config = new Config($annotationReader->getConfigs());
        $this->annotationReader->setConfig($this->config);
    }

    public function isAuthorize(?string $routeName)
    {
        if (in_array($this->kernel->getEnvironment(), ['dev', 'test'])) {
            $this->annotationReader->readUserActionAnnotations();
        }

        $userAction = $this->getRoleAction($routeName);
        if ($userAction and $userAction->getHasAuth()) {
            $auth = $this->getAuth();

            if (($condition = $userAction->getCondition()) !== null) {
                $condition = $this->resolveCondition($condition, $routeName);
                $container = $this->annotationReader->getContainer();
                $request = $this->requestStack->getMainRequest();
                if ($userAction->getConditionOption() === RouteAction::CONDITION_OVERWRITE) {
                    return $condition($auth, $userAction, $container, $request);
                } else {
                    return $condition($auth, $userAction, $container, $request) and $auth?->hasAction($userAction);
                }
            }

            return $auth?->hasAction($userAction);
        }
        return true;
    }

    private function resolveCondition(string $condition, string $routeName): Closure
    {
        if ($resolved = ($this->resolvedConditions[$routeName] ?? null)) {
            return $resolved;
        }
        $condition = json_decode($condition) ?? $condition;
        try {
            $object = $this->annotationReader->getContainer()->get(is_string($condition) ? $condition : $condition[0]);
        } catch (\Exception) {
            try {
                $object = new (is_string($condition) ? $condition : $condition[0]);
            } catch (\Exception $e) {
                throw new SymfropBaseException($e->getMessage());
            }
        }
        $this->resolvedConditions[$routeName] = fn (?UserInterface $auth, ActionInterface $userAction, ContainerInterface $container, Request $request) => is_string($condition)
            ? $object($auth, $userAction, $container, $request)
            : $object->$condition[0]($auth, $userAction, $container, $request);
        return $this->resolvedConditions[$routeName];
    }

    private function getRoleAction(?string $routeName): ?ActionInterface
    {
        $userAction = $this->userActions[$routeName] ?? null;
        if ($userAction === null) {
            $this->userActions[$routeName] = $this->manager->getRepository(
                $this->config->getActionEntity()
            )->findOneBy(['name' => $routeName]);
        }
        return $this->userActions[$routeName];
    }

    public function getAuth(): null|UserUserInterface| UserInterface
    {
        if (is_null($this->auth)) {
            $this->auth = $this->security->getUser();
        }
        return $this->auth;
    }

    /**
     * @return AnnotationReader
     */
    public function getAnnotationReader(): AnnotationReader
    {
        return $this->annotationReader;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->manager;
    }

    /**
     * @return array
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    public function setSecurity(Security $security): self
    {
        $this->security = $security;
        $this->auth = $security->getUser();
        return $this;
    }
}
