<?php

namespace Njeaner\Symfrop\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Njeaner\Symfrop\Core\Annotation\Route;
use Njeaner\Symfrop\Core\Annotation\RouteAction;
use Njeaner\Symfrop\Core\Annotation\RouteActionInterface;
use Njeaner\Symfrop\Core\Manager\AnnotationManager;
use Njeaner\Symfrop\Core\Manager\AnnotationReader;
use Njeaner\Symfrop\Core\Manager\Exceptions\AnnotationReaderException;
use Njeaner\Symfrop\Core\Service\Config;
use Njeaner\Symfrop\Core\Service\CONSTANTS;
use Njeaner\Symfrop\Entity\Contract\UserInterface;
use Njeaner\Symfrop\Entity\User;
use Njeaner\Symfrop\Exceptions\SymfropTestException;
use Njeaner\Symfrop\SymfropBundle;
use Njeaner\Symfrop\Tests\Controller\AuthCondition;
use Njeaner\Symfrop\Tests\Controller\ClassAttributeRepeated;
use Njeaner\Symfrop\Tests\Controller\MethodAttributeRepeated;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class SymfropTest extends WebTestCase
{
    private ?EntityManagerInterface $manager = null;

    public function testSymfropControllerSimpleActions()
    {
        $self = $this;
        $client = static::createClient();
        $container = static::getContainer();
        $manager = $this->getManager($container);
        $manager->beginTransaction();
        $hasher = $container->get('security.user_password_hasher');
        $client->request('GET', '/');
        $config = Config::getInstance();
        /** @var AnnotationManager */
        $annotationManager = $container->get('njeaner_symfrop.annotation_manager');
        $this->assertTrue($annotationManager->isAuthorize('njeaner_symfrop_user_index'));
        $this->assertTrue($annotationManager->isAuthorize('njeaner_symfrop_role_index'));
        $this->assertFalse($annotationManager->isAuthorize('njeaner_symfrop_role_create'));
        $this->assertFalse($annotationManager->isAuthorize('njeaner_symfrop_role_update'));
        $this->assertFalse($annotationManager->isAuthorize('njeaner_symfrop_role_delete'));
        $this->assertFalse($annotationManager->isAuthorize('njeaner_symfrop_action_edit'));
        /** @var User $user */
        $user = new ($config->getUserEntity());
        $user
            ->setPseudo('Auth Test')
            ->setPassword(
                $hasher->hashPassword($user, 'mot2passe')
            )
            ->setUsername('Auth Test')
            ->setIsLocked(false)
            ->setRole($self->getRoleRepository($container)->findOneBy(['name' => CONSTANTS::ROLE_SUPERADMIN]));
        $this->getUserRepository($container)->add($user, true);
        $token = new TestBrowserToken($user->getRoles(), $user, 'main');
        $container->get('security.token_storage')->setToken($token);
        $annotationManager->setSecurity(new Security($container));
        $this->assertTrue($annotationManager->isAuthorize('njeaner_symfrop_role_create'));
        $this->assertTrue($annotationManager->isAuthorize('njeaner_symfrop_role_update'));
        $this->assertTrue($annotationManager->isAuthorize('njeaner_symfrop_role_delete'));
        $this->assertTrue($annotationManager->isAuthorize('njeaner_symfrop_action_edit'));
        $this->assertTrue($annotationManager->isAuthorize('njeaner_symfrop_user_role_edit'));
        $this->assertTrue($annotationManager->isAuthorize('njeaner_symfrop_user_index'));
        $this->assertTrue($annotationManager->isAuthorize('njeaner_symfrop_role_index'));
        $manager->rollback();
        $this->assertCount(0, $this->getUserRepository($container)->findAll());
    }

    public function testClassAttributeRepetitionIsNotAuthorized()
    {
        $client = static::createClient();
        $container = static::getContainer();
        $manager = $this->getManager($container);
        $manager->beginTransaction();
        $client->request('GET', '/');

        $config = Config::getInstance();
        $reflection = new \ReflectionClass($config);
        $resources = $reflection->getProperty('resources');
        $resources->setAccessible(true);
        $resources->setValue($config, [
            'Njeaner\\Symfrop\\Tests\\Controller\\ClassAttributeRepeated' =>  __DIR__ . '/Controller/ClassAttributeRepeated.php'
        ]);
        $resources->setAccessible(false);
        /** @var AnnotationReader */
        $annotationReader = $container->get(AnnotationReader::class);
        $this->expectException(AnnotationReaderException::class);
        $this->expectExceptionMessage(
            'Several instance of "' . RouteActionInterface::class .
                '" attributes has been detected in "' . ClassAttributeRepeated::class . '" attributes ('
                . join(', ', [RouteAction::class, Route::class])
                . '). Use of more than one attribute of this instance is not permit in the same class attributes'
        );
        $annotationReader->readUserActionAnnotations();

        $resources = $reflection->getProperty('resources');
        $resources->setAccessible(true);
        $resources->setValue($config, [
            'Njeaner\\Symfrop\\Tests\\Controller\\MethodAttributeRepeated' =>  __DIR__ . '/Controller/MethodAttributeRepeated.php'
        ]);
        $resources->setAccessible(false);
        /** @var AnnotationReader */
        $annotationReader = $container->get(AnnotationReader::class);
        $this->expectException(AnnotationReaderException::class);
        $this->expectExceptionMessage(
            'Several instance of "' . RouteActionInterface::class . '" attributes has been detected in "'
                . MethodAttributeRepeated::class . '::index" method attributes ('
                . join(', ', [RouteAction::class, RouteAction::class])
                . '). Use of more than one attribute of this instance is not permit in the same class method attributes'
        );
        $annotationReader->readUserActionAnnotations();
        $manager->rollback();
    }


    public function testMethodAttributeRepetitionIsNotAuthorized()
    {
        $client = static::createClient();
        $container = static::getContainer();
        $manager = $this->getManager($container);
        $manager->beginTransaction();
        $client->request('GET', '/');

        $config = Config::getInstance();
        $reflection = new \ReflectionClass($config);
        $resources = $reflection->getProperty('resources');
        $resources->setAccessible(true);
        $resources->setValue($config, [
            MethodAttributeRepeated::class =>  __DIR__ . '/Controller/MethodAttributeRepeated.php'
        ]);
        $resources->setAccessible(false);
        /** @var AnnotationReader */
        $annotationReader = $container->get(AnnotationReader::class);
        $this->expectException(AnnotationReaderException::class);
        $this->expectExceptionMessage(
            'Several instance of "' . RouteActionInterface::class . '" attributes has been detected in "'
                . MethodAttributeRepeated::class . '::index" method attributes ('
                . join(', ', [RouteAction::class, RouteAction::class])
                . '). Use of more than one attribute of this instance is not permit in the same class method attributes'
        );
        $annotationReader->readUserActionAnnotations();
        $manager->rollback();
    }

    public function testAuthHasCondition()
    {
        $client = static::createClient();
        $container = static::getContainer();
        $manager = $this->getManager($container);
        $manager->beginTransaction();
        $client->request('GET', '/');
        $config = Config::getInstance();
        $reflection = new \ReflectionClass($config);
        $resources = $reflection->getProperty('resources');
        $resources->setAccessible(true);
        $resources->setValue($config, [
            AuthCondition::class =>  __DIR__ . '/Controller/AuthCondition.php'
        ]);
        $resources->setAccessible(false);
        /** @var AnnotationReader */
        $annotationReader = $container->get(AnnotationReader::class);
        $annotationReader->readUserActionAnnotations();
        $hasher = $container->get('security.user_password_hasher');
        /** @var AnnotationManager */
        $annotationManager = $container->get('njeaner_symfrop.annotation_manager');
        $this->assertTrue($annotationManager->isAuthorize('action1'));
        $this->assertFalse($annotationManager->isAuthorize('action2'));
        $this->assertFalse($annotationManager->isAuthorize('action3'));
        $this->assertFalse($annotationManager->isAuthorize('action4'));
        //--------------------------------------------------------------------------
        /** @var User $user */
        $user = new ($config->getUserEntity());
        $user
            ->setPseudo('Auth Test 1')
            ->setPassword(
                $hasher->hashPassword($user, 'mot2passe')
            )
            ->setUsername('Auth Test 1')
            ->setIsLocked(false)
            ->setRole($roleAdmin = $this->getRoleRepository($container)->findOneBy(['name' => CONSTANTS::ROLE_SUPERADMIN]));
        $this->getUserRepository($container)->add($user, true);
        $token = new TestBrowserToken($user->getRoles(), $user, 'main');
        $container->get('security.token_storage')->setToken($token);
        $annotationManager->setSecurity(new Security($container));
        $this->assertTrue($annotationManager->isAuthorize('action1'));
        $this->assertTrue($annotationManager->isAuthorize('action2'));
        $this->assertTrue($annotationManager->isAuthorize('action3'));
        $this->assertTrue($annotationManager->isAuthorize('action4'));

        /** @var User $user */
        $user = new ($config->getUserEntity());
        $user
            ->setPseudo('Auth Test 2')
            ->setPassword(
                $hasher->hashPassword($user, 'mot2passe')
            )
            ->setUsername('Auth Test 2')
            ->setIsLocked(false)
            ->setRole($roleAdmin);
        $this->getUserRepository($container)->add($user, true);
        // ------------------------------------------------------------------------
        $token = new TestBrowserToken($user->getRoles(), $user, 'main');
        $container->get('security.token_storage')->setToken($token);
        $annotationManager->setSecurity(new Security($container));
        $this->assertTrue($annotationManager->isAuthorize('action1'));
        $this->assertFalse($annotationManager->isAuthorize('action2'));
        $this->assertTrue($annotationManager->isAuthorize('action3'));
        $this->assertFalse($annotationManager->isAuthorize('action4'));
        $manager->rollback();
    }


    private function getManager(ContainerInterface $container): EntityManagerInterface
    {
        if (null === $this->manager) {
            $this->manager = $this->getDoctrine($container)->getManager();
        }
        return $this->manager;
    }

    private function getDoctrine(ContainerInterface $container): ManagerRegistry
    {
        return $container->get('doctrine');
    }

    private function getRoleRepository(ContainerInterface $container): EntityRepository
    {
        return $this->getManager($container)->getRepository(Config::getInstance()->getRoleEntity());
    }

    private function getUserRepository(ContainerInterface $container): EntityRepository
    {
        return $this->getManager($container)->getRepository(Config::getInstance()->getUserEntity());
    }

    private function getActionRepository(ContainerInterface $container): EntityRepository
    {
        return $this->getManager($container)->getRepository(Config::getInstance()->getActionEntity());
    }

    private function seedUser(ContainerInterface $container)
    {
        $hasher = $container->get('security.user_password_hasher');
        $manager = $this->getManager($container);
        if (empty($this->getUserRepository($container)->findAll())) {
            foreach ([
                ['user1@gmail.com', 'ROLE_USER', 'mot2passe'],
                ['user2@gmail.com', 'ROLE_USER', 'mot2passe'],
                ['user3@gmail.com', 'ROLE_USER', 'mot2passe'],
                ['user4@gmail.com', 'ROLE_USER', 'mot2passe'],
                ['admin1@gmail.com', 'ROLE_ADMIN', 'mot2passe'],
                ['admin2@gmail.com', 'ROLE_ADMIN', 'mot2passe'],
                ['superadmin1@gmail.com', 'ROLE_SUPERADMIN', 'mot2passe'],
                ['superadmin2@gmail.com', 'ROLE_SUPERADMIN', 'mot2passe']
            ] as $item) {
                $user = (new (Config::getInstance()->getUserEntity())())
                    ->setEmail($item[0])
                    ->setRole(
                        $this->getRoleRepository($container)->findOneBy(['name' => $item[1]])
                    );
                $manager->persist(
                    $user->setPassword($hasher->hashPassword($user, $item[2]))
                );
            }

            $manager->flush();
        }
    }
}
