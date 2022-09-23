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

    /**
     * @var SavedAction[]
     */
    private $routes = [];

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

        /** @var SavedAction */
        if (($action = $this->routes[$routeName] ?? false)) {
            if ($action->getResponseType() === SavedAction::IS_BOOLEAN_RESPONSE) {
                if ($action->isAuthChanged($this->getAuth())) {
                    $userAction = $this->userActions[$routeName];
                    return $this->getAuth()->hasAction($userAction);
                }
                return $action->getPermission();
            } else {
                if ($action->getOption() === RouteAction::CONDITION_OVERWRITE) {
                    return $action->callCondition($this->getAuth());
                } else {
                    $auth = $this->getAuth();
                    if ($action->getOption() === RouteAction::CONDITION_CHECK_ONE) {
                        return (($auth?->hasAction($this->userActions[$routeName])) ?  true : false)
                            ||  ($action->callCondition($this->getAuth()));
                    }
                    return (($auth?->hasAction($this->userActions[$routeName])) ?  true : false)
                        && ($action->callCondition($this->getAuth()));
                }
            }
        }

        $userAction = $this->manager->getRepository(
            $this->config->getActionEntity()
        )->findOneBy(['name' => $routeName]);

        if ($userAction) {
            $this->userActions[$routeName] = $userAction;
            /** @var ActionInterface $userAction */
            if ($userAction->getHasAuth()) {
                /** @var UserInterface $auth */
                $auth = $this->getAuth();
                if (($condition = $userAction->getCondition()) !== null) {

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

                    $container = $this->annotationReader->getContainer();
                    $request = $this->requestStack->getMainRequest();

                    if (($conditionOption = $userAction->getConditionOption()) === RouteAction::CONDITION_OVERWRITE) {
                        return $this->ChechAndStoreAuthorized(
                            $routeName,
                            $this->chechCondition($condition, $object, $userAction, $container, $request, true, $conditionOption),
                            $conditionOption
                        );
                    } else {
                        return $this->ChechAndStoreAuthorized(
                            $routeName,
                            $this->chechCondition($condition, $object, $userAction, $container, $request, (((bool) $auth?->hasAction($userAction)) ? true : false), $conditionOption),
                            $conditionOption
                        );
                    }
                } else {
                    $permission = $this->ChechAndStoreAuthorized($routeName, ((bool) $auth?->hasAction($userAction)) ? true : false, 0);
                    return $permission;
                }
            }
            return $this->ChechAndStoreAuthorized($routeName, true, 0);
        }
        return $this->ChechAndStoreAuthorized($routeName, true, 0);
    }

    private function chechCondition(
        string|array $condition,
        object $object,
        ActionInterface $userAction,
        ContainerInterface $container,
        ?Request $request,
        ?bool $addCondition = null,
        int $addedOption = RouteAction::CONDITION_AT_TIME
    ): Closure {

        return function (?UserInterface $auth) use (
            $condition,
            $object,
            $userAction,
            $container,
            $request,
            $addCondition,
            $addedOption
        ) {
            /** @var bool */
            $resolve = (is_string($condition)
                ? $object($auth, $userAction, $container, $request)
                : $object->{$condition[1]}($auth, $userAction, $container, $request));

            if ($addCondition) {
                if ($addedOption === RouteAction::CONDITION_CHECK_ONE) {
                    return $addCondition || $resolve;
                }
                return $addCondition && $resolve;
            } else {
                return $resolve;
            }
        };
    }

    private function ChechAndStoreAuthorized(?string $routeName, bool|Closure $condition, int $option): bool
    {
        $resolve = $condition;
        if ($routeName === null) {
            $this->routes[$routeName] = new SavedAction(
                false,
                $routeName ?? 'UNDEFINDE_ROUTE_NAME',
                null,
                null,
                null,
                SavedAction::IS_BOOLEAN_RESPONSE
            );
            return false;
        }
        $condition = is_bool($resolve) ? $condition : $condition($this->getAuth());

        $this->routes[$routeName] = new SavedAction(
            $condition === true ? true : false,
            $routeName,
            $this->getAuth(),
            is_bool($resolve) ? null : $resolve,
            is_bool($resolve) ? null : $option,
            is_bool($resolve) ? SavedAction::IS_BOOLEAN_RESPONSE : SavedAction::IS_CONDITIONAL_RESPONSE
        );
        return $condition;
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


class SavedAction
{
    const IS_BOOLEAN_RESPONSE = 1;
    const IS_AUTH_RESPONSE = 2;
    const IS_CONDITIONAL_RESPONSE = 3;

    public function __construct(
        readonly private bool $permission,
        readonly private string $route,
        readonly private ?UserInterface $auth,
        readonly private ?Closure $condition,
        readonly private ?int $option,
        readonly private int $responseType
    ) {
    }

    public function getPermission(): bool
    {
        return $this->permission;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getAuth(): ?UserInterface
    {
        return $this->auth;
    }

    public function getCondition(): ?callable
    {
        return $this->condition;
    }

    public function getOption(): ?int
    {
        return $this->option;
    }

    public function callCondition(UserInterface $auth)
    {
        $condition = $this->condition;
        return $condition($auth);
    }

    public function getResponseType()
    {
        return $this->responseType;
    }

    public function isAuthChanged(?UserInterface $auth): bool
    {
        return $this->auth !== null &&  ($this->auth !== $auth);
    }
}
