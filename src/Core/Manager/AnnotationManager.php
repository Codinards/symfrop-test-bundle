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
 * @version 0.0.1
 */
class AnnotationManager
{

    protected EntityManagerInterface $manager;

    protected Config $config;

    protected ?UserUserInterface $auth = null;

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

        if (($action = $this->routes[$routeName] ?? false)) {
            if (count($action[1]) <= 2) {
                /** ------------------------------- only use during test ----------------------- */
                if (($this->getAuth() !== null) && ($action[1]['auth'] !== $this->getAuth())) {
                    $userAction = $this->userActions[$routeName];
                    return $this->getAuth()->hasAction($userAction);
                }
                /**----------------------------------------------------------------------------- */
                return $action[0];
            } else {
                if ($action[1]['option'] === RouteAction::CONDITION_OVERWRITE) {
                    return $action[1]['condition']($this->getAuth());
                } else {
                    $auth = $this->getAuth();
                    if ($action[1]['option'] === RouteAction::CONDITION_CHECK_ONE) {
                        return (($auth?->hasAction($this->userActions[$routeName])) ?  true : false)
                            ||  ($action[1]['condition']($this->getAuth()));
                    } else {
                        return (($auth?->hasAction($this->userActions[$routeName])) ?  true : false)
                            && ($action[1]['condition']($this->getAuth()));
                    }
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
                            $this->chechCondition($condition, $object, $auth, $userAction, $container, $request, true, $conditionOption),
                            $conditionOption
                        );
                    } else {
                        return $this->ChechAndStoreAuthorized(
                            $routeName,
                            $this->chechCondition($condition, $object, $auth, $userAction, $container, $request, (((bool) $auth?->hasAction($userAction)) ? true : false), $conditionOption),
                            $conditionOption
                        );
                    }
                } else {
                    return $this->ChechAndStoreAuthorized($routeName, ((bool) $auth?->hasAction($userAction)) ? true : false);
                }
            }
            return $this->ChechAndStoreAuthorized($routeName, true);
        }
        return $this->ChechAndStoreAuthorized($routeName, true);
    }

    private function chechCondition(
        string|array $condition,
        object $object,
        null|UserInterface|UserUserInterface $auth,
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

    private function ChechAndStoreAuthorized(?string $routeName, bool|Closure $condition, int $option = 0): bool
    {
        $resolve = $condition;
        if ($routeName === null) {
            $this->routes[$routeName] = ['unauthorized', $routeName];
            return false;
        }
        $condition = is_bool($resolve) ? $condition : $condition($this->getAuth());
        $permission = $condition === true ? true : false;
        $this->routes[$routeName] = [$permission, is_bool($resolve) ?
            ['route' => $routeName, 'auth' => $this->getAuth()]
            : ['condition' => $resolve, 'option' => $option, 'auth' => $this->getAuth()]];
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
