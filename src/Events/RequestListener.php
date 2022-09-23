<?php

namespace Njeaner\Symfrop\Events;

use Doctrine\ORM\EntityManagerInterface;
use Njeaner\Symfrop\Core\Manager\AnnotationManager;
use Njeaner\Symfrop\Core\Service\Config;
use Njeaner\Symfrop\Entity\Contract\RoleInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class RequestListener
{
    protected Config $config;

    protected EntityManagerInterface $manager;

    public function __construct(
        protected AnnotationManager $annotationManager,
    ) {
        $this->config = $this->annotationManager->getConfig();
        $this->manager = $this->annotationManager->getEntityManager();
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $this->createRole();
        if (
            !$event->getRequest()->attributes->get('exception')
            and !$this->annotationManager->isAuthorize(
                $event->getRequest()->attributes->get('_route')
            )
            and !str_contains($event->getRequest()->attributes->get('_controller'), 'web_profiler.controller')
        ) {
            throw new UnauthorizedHttpException('Unauthorize page Action');
        }
    }

    public function createRole()
    {
        $roleEntityName = $this->config->getRoleEntity();
        if (empty($this->manager->getRepository($roleEntityName)->findAll())) {
            $roleItems = $this->config->getRoles();
            foreach ($roleItems as $roleName => $title) {
                /** @var RoleInterface $role */
                $role = (new $roleEntityName())->setName($roleName);
                if (is_array($title)) {
                    $role->setTitle($title[0])->setIsDeletable($title[1]);
                } else {
                    $role->setTitle($title);
                }
                $this->manager->persist($role);
            }

            $this->manager->flush();
        }
    }
}
