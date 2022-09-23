<?php

namespace Njeaner\Symfrop\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Njeaner\Symfrop\Core\Annotation\Route as RouteAction;
use Njeaner\Symfrop\Core\Service\Config;
use Njeaner\Symfrop\Core\Service\CONSTANTS;
use Njeaner\Symfrop\Entity\Contract\ActionInterface;
use Njeaner\Symfrop\Entity\Contract\RoleInterface;
use Njeaner\Symfrop\Entity\Contract\UserInterface;
use Njeaner\Symfrop\Form\RoleType;
use Njeaner\Symfrop\Permissions\ActionPermissions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Twig\Environment;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
#[RouteAction(path: '/symfrop/{_locale<fr|en|es|pt>?en}/'/*, name: self::BASE_NAME*/)]
class SymfropController extends AbstractController
{

    protected ?Config $config = null;

    static $TEMPLATE = '@Symfrop/roles';

    // const BASE_NAME = 'njeaner_symfrop_';

    public function __construct(
        private FormFactoryInterface $formFactory,
        protected EntityManagerInterface $manager,
        protected RequestStack $requestStack,
        protected Environment $twig,
        private TokenStorageInterface $security
    ) {
        $this->config =  Config::getInstance();
    }

    public static function getSubscribedServices(): array
    {
        return [
            'router' => '?' . RouterInterface::class,
            'request_stack' => '?' . RequestStack::class,
            'http_kernel' => '?' . HttpKernelInterface::class,
            'serializer' => '?' . SerializerInterface::class,
            'security.authorization_checker' => '?' . AuthorizationCheckerInterface::class,
            'twig' => '?' . Environment::class,
            'form.factory' => '?' . FormFactoryInterface::class,
            'security.token_storage' => '?' . TokenStorageInterface::class,
            'security.csrf.token_manager' => '?' . CsrfTokenManagerInterface::class,
            'parameter_bag' => '?' . ContainerBagInterface::class,
        ];
    }

    #[RouteAction(
        path: 'roles/index',
        name: CONSTANTS::ROLE_INDEX,
        methods: ['GET'],
        title: 'symfrop role index page action',
        target: CONSTANTS::ROLE_ALL_ADMINS,
        isIndex: true,
        actionCondition: ActionPermissions::class,
        conditionOption: RouteAction::CONDITION_CHECK_ONE
    )]
    public function index(): Response
    {
        return $this->render(self::$TEMPLATE . '/index.html.twig', [
            'roles' => $this->getManager()->getRepository($this->config->getRoleEntity())->findAll()
        ]);
    }

    #[RouteAction(
        path: 'users/index',
        name: CONSTANTS::USER_INDEX,
        methods: ['GET'],
        title: 'symfrop user index page action',
        target: CONSTANTS::ROLE_ALL_ADMINS,
        actionCondition: ActionPermissions::class,
        conditionOption: RouteAction::CONDITION_CHECK_ONE
    )]
    public function users(): Response
    {
        return $this->render(self::$TEMPLATE . '/users_index.html.twig', [
            'users' => (new ArrayCollection(
                $this->getManager()
                    ->getRepository($this->config->getUserEntity())->findAll()
            ))->filter(fn ($user) => $user->getUserIdentifier() !== $this->getAuth()?->getUserIdentifier())
        ]);
    }


    #[RouteAction(
        path: 'roles/new',
        name: CONSTANTS::ROLE_CREATE,
        methods: ['GET', "POST"],
        title: 'symfrop role creation page action',
        target: CONSTANTS::ROLE_ALL_ADMINS
    )]
    public function createRole(Request $request)
    {
        RoleType::setManager($manager = $this->manager);
        /** @var RoleInterface $role */
        $role = (new ($this->config->getRoleEntity())());
        $form = $this->createForm(Config::getInstance()->getRoleForm(), $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            /** @var ActionInterface[] $notHasAuthAction */
            $notHasAuthAction = $manager->getRepository($this->config->getActionEntity())->findBy(['hasAuth' => false]);
            foreach ($notHasAuthAction as $action) {
                $role->addAction($action);
            }
            $manager->persist($role);
            $manager->flush();
            return $this->redirectToRoute('njeaner_symfrop_role_index');
        }

        return $this->render(self::$TEMPLATE . '/create_role.html.twig', [
            'form' => $form->createView(),
            'title' => 'New Role Creation'
        ]);
    }


    #[RouteAction(
        path: 'roles/{role<\d+>}-update',
        name: CONSTANTS::ROLE_UPDATE,
        methods: ['GET', 'POST'],
        title: 'symfrop role update page action',
        target: CONSTANTS::ROLE_ALL_ADMINS
    )]
    public function updateRole(Request $request, int $role)
    {
        RoleType::setManager($manager = $this->manager);
        /** @var RoleInterface $role */
        $role = $manager->getRepository($this->config->getRoleEntity())->find($role);
        if (!$role) {
            throw new NotFoundHttpException('This role does not exists');
        }

        if ($role->getName() === CONSTANTS::ROLE_SUPERADMIN) {
            $this->addFlash('error', 'The ' . $role->getTitle() . ' role is not updatable');
            return $this->redirectToRoute('njeaner_symfrop_role_index');
        }

        $form = $this->createForm(Config::getInstance()->getRoleForm(), $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $manager->flush();
            return $this->redirectToRoute('njeaner_symfrop_role_index');
        }
        return $this->render(self::$TEMPLATE . '/create_role.html.twig', [
            'form' => $form->createView(),
            'title' => 'Role Edition'
        ]);
    }

    #[RouteAction(
        path: 'roles/{role<\d+>}-delete',
        name: CONSTANTS::ROLE_DELETE,
        methods: ['POST'],
        title: 'symfrop role delete page action',
        target: CONSTANTS::ROLE_SUPERADMIN
    )]
    public function deleteRole(Request $request, int $role, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $token = new CsrfToken('symfrop_role_delete', $request->request->get('_csrf_token'));
        if ($csrfTokenManager->isTokenValid($token)) {
            $manager = $this->getManager();
            /** @var RoleInterface $role */
            $role = $manager->getRepository($this->config->getRoleEntity())->find($role);
            if (!$role) {
                throw new NotFoundHttpException('This role does not exists');
            }

            if ($role->getIsDeletable()) {
                if (!in_array(
                    $role->getName(),
                    array_keys($this->config->getRoles())
                )) {
                    if ($role->getUsers()->isEmpty()) {
                        $manager->remove($role);
                        $manager->flush();
                        $this->addFlash('success', 'The ' . $role->getTitle() . ' role has been successfully removed from the database');
                        return $this->redirectToRoute('njeaner_symfrop_role_index');
                    } else {
                        $count = $role->getUsers()->count();
                        $this->addFlash(
                            'error',
                            $count . ' member' . ($count > 1 ? 's are ' : ' is ')
                                . 'using ' . $role->getTitle() . ' role' . ' actually. Please change '
                                . ($count > 1 ? 'them' : 'him') . ' the role before deleting this role'
                        );
                        return $this->redirectToRoute('njeaner_symfrop_role_index');
                    }
                }
            }
            $this->addFlash('error', 'The ' . $role->getTitle() . ' role is not deletable. It is a SymfropBundle base role.');
            return $this->redirectToRoute('njeaner_symfrop_role_index');
        }
        $this->addFlash('error', 'Some violation has been detected');
        return $this->redirectToRoute('njeaner_symfrop_role_index');
    }

    #[RouteAction(
        path: 'roles/{user<\d+>}-user-role-edit',
        name: CONSTANTS::USER_ROLE_EDIT,
        methods: ['GET', 'POST'],
        title: 'symfrop user role edit page action',
        target: CONSTANTS::ROLE_SUPERADMIN,
        actionCondition: [ActionPermissions::class, 'ifOnlyExistsTheFirstAdmin'],
        conditionOption: RouteAction::CONDITION_CHECK_ONE
    )]
    public function userRoleEdit(Request $request, int $user): Response
    {
        if ($this->getAuth()?->getId() === $user) {
            $this->addFlash('error', 'Sorry! You can not change our own role');
            return $this->redirectToRoute('njeaner_symfrop_role_index');
        }

        $manager = $this->getManager();
        /** @var UserInterface $user */
        $user = $manager->getRepository($this->config->getUserEntity())->find($user);
        if (!$user) {
            throw new NotFoundHttpException('This user does not exists');
        }
        $form = $this->createForm(Config::getInstance()->getUserRoleForm(), $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'The user role has been successfully updated');
            return $this->redirectToRoute('njeaner_symfrop_role_index');
        }
        return $this->render(self::$TEMPLATE . '\user_role_edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[RouteAction(
        path: 'roles/{action<\d+>}-action-edit',
        name: CONSTANTS::ACTION_EDIT,
        title: 'symfrop action edit page action',
        target: CONSTANTS::ROLE_ALL_ADMINS
    )]
    public function actionEdit(Request $request, int $action): Response
    {
        $forceAction = (bool) $request->query->get('force');
        $manager = $this->getManager();
        /** @var ActionInterface $action */
        $action = $manager->getRepository($this->config->getActionEntity())->find($action);
        if (!$action) {
            throw new NotFoundHttpException('This action does not exists');
        }
        if ($action->getIsUpdatable() === false) {
            if ($forceAction !== true) {
                $this->addFlash('error', 'The action is not updatable');
                return $this->redirectToRoute('njeaner_symfrop_role_index');
            } else {
                if ($this->getAuth()->getRole()->getName() !== CONSTANTS::ROLE_SUPERADMIN) {
                    $this->addFlash('error', 'The action is not updatable');
                    return $this->redirectToRoute('njeaner_symfrop_role_index');
                }
            }
        }
        $form = $this->createForm(Config::getInstance()->getActionForm(), $action);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $manager->persist($action);
            $manager->flush();
            $this->addFlash('success', 'The action has been successfully updated');
            return $this->redirectToRoute('njeaner_symfrop_role_index');
        }
        return $this->render(self::$TEMPLATE . '\action_edit.html.twig', [
            'user' => $action,
            'form' => $form->createView()
        ]);
    }

    protected function getManager(): EntityManagerInterface
    {
        return $this->manager;
    }

    public function createFormFactory(): FormFactoryInterface
    {
        $csrfGenerator = new UriSafeTokenGenerator();
        $csrfStorage = new SessionTokenStorage($this->requestStack);
        $csrfManager = new CsrfTokenManager($csrfGenerator, $csrfStorage);
        return Forms::createFormFactoryBuilder()
            ->addExtension(new CsrfExtension($csrfManager))
            ->getFormFactory();
    }


    private function getAuth(): ?UserInterface
    {
        return $this->getUser();
    }

    /** 
     * Returns a rendered view.
     * */
    protected function renderView(string $view, array $parameters = []): string
    {
        return $this->twig->render($view, $parameters);
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     */
    protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface
    {
        return $this->formFactory->create($type, $data, $options);
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser(): ?UserInterface
    {
        return $this->security->getToken()?->getUser();
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters = array_merge($parameters, $this->config->getAll()['templates']);
        return parent::render($view, $parameters, $response);
    }

    public function getConfig(): ?Config
    {
        return $this->config;
    }

    public function setConfig(?Config $config): self
    {
        $this->config = $config;

        return $this;
    }
}
