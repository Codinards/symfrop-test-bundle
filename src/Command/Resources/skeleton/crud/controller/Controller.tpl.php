<?= "<?php\n" ?>
namespace <?= $namespace ?>;

<?= $use_statements; ?>

#[Route('<?= $route_path ?>/{_locale<fr|en|es|pt>?en}', requirements: ['_locale' => 'fr|en|es'])]
    class <?= $class_name ?> extends AbstractController
    {
    <?= $generator->generateRouteForControllerMethod('', sprintf('%s_index', $route_name), ['GET']) ?>
    <?php if (isset($repository_full_class_name)) : ?>
        #[RouteAction('<?= $route_name ?>', '<?= $route_name ?>', CONSTANTS::ROLE_ALL, isIndex: true )]
        public function index(<?= $repository_class_name ?> $<?= $repository_var ?>): Response
        {
        return $this->render('<?= $templates_path ?>/index.html.twig', [
        '<?= $entity_twig_var_plural ?>' => $<?= $repository_var ?>->findAll(),
        ]);
        }
    <?php else : ?>
        #[RouteAction('<?= $route_name ?>', '<?= $route_name ?>', CONSTANTS::ROLE_ALL, isIndex: true )]
        public function index(EntityManagerInterface $entityManager): Response
        {
        $<?= $entity_var_plural ?> = $entityManager
        ->getRepository(<?= $entity_class_name ?>::class)
        ->findAll();

        return $this->render('<?= $templates_path ?>/index.html.twig', [
        '<?= $entity_twig_var_plural ?>' => $<?= $entity_var_plural ?>,
        ]);
        }
    <?php endif ?>

    <?= $generator->generateRouteForControllerMethod('/new', sprintf('%s_new', $route_name), ['GET', 'POST']) ?>
    #[RouteAction('<?= sprintf('%s_new', $route_name) ?>', '<?= sprintf('%s_new', $route_name) ?>', CONSTANTS::ROLE_ALL_ADMINS)]
    <?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
        public function new(Request $request, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
    <?php } else { ?>
        public function new(Request $request, EntityManagerInterface $entityManager): Response
    <?php } ?>
    {
    $<?= $entity_var_singular ?> = new <?= $entity_class_name ?>();
    $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
    $form->handleRequest($request);

    <?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
        if ($form->isSubmitted() && $form->isValid()) {
        $<?= $repository_var ?>->add($<?= $entity_var_singular ?>, true);
        $this->addFlash('success', 'the entity has been successfully created.');

        return $this->redirectToRoute('<?= $route_name ?>_index', ['_locale' => $request->getlocale()], Response::HTTP_SEE_OTHER);
        }
    <?php } else { ?>
        if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($<?= $entity_var_singular ?>);
        $entityManager->flush();
        $this->addFlash('success', 'the entity has been successfully created.');

        return $this->redirectToRoute('<?= $route_name ?>_index', ['_locale' => $request->getlocale()], Response::HTTP_SEE_OTHER);
        }
    <?php } ?>

    <?php if ($use_render_form) { ?>
        return $this->renderForm('<?= $templates_path ?>/new.html.twig', [
        '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
        'form' => $form,
        ]);
    <?php } else { ?>
        return $this->render('<?= $templates_path ?>/new.html.twig', [
        '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
        'form' => $form->createView(),
        ]);
    <?php } ?>
    }

    <?= $generator->generateRouteForControllerMethod(sprintf('/{%s}', $entity_identifier), sprintf('%s_show', $route_name), ['GET']) ?>
    #[RouteAction('<?= sprintf('%s_show', $route_name) ?>', '<?= sprintf('%s_show', $route_name) ?>', CONSTANTS::ROLE_ALL)]
    public function show(<?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
    return $this->render('<?= $templates_path ?>/show.html.twig', [
    '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
    ]);
    }

    <?= $generator->generateRouteForControllerMethod(sprintf('/{%s}/edit', $entity_identifier), sprintf('%s_edit', $route_name), ['GET', 'POST']) ?>
    #[RouteAction('<?= sprintf('%s_edit', $route_name) ?>', '<?= sprintf('%s_edit', $route_name) ?>', CONSTANTS::ROLE_ALL_ADMINS)]
    <?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
        public function edit(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
    <?php } else { ?>
        public function edit(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, EntityManagerInterface $entityManager): Response
    <?php } ?>
    {
    $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
    $form->handleRequest($request);

    <?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
        if ($form->isSubmitted() && $form->isValid()) {
        $<?= $repository_var ?>->add($<?= $entity_var_singular ?>, true);
        $this->addFlash('success', 'the entity has been successfully updated.');

        return $this->redirectToRoute('<?= $route_name ?>_show', ['id' => $<?= $entity_var_singular ?>->getId(), '_locale' => $request->getlocale()], Response::HTTP_SEE_OTHER);
        }
    <?php } else { ?>
        if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'the entity has been successfully updated.');

        return $this->redirectToRoute('<?= $route_name ?>_show', ['id' => $<?= $entity_var_singular ?>->getId(), '_locale' => $request->getlocale()], Response::HTTP_SEE_OTHER);
        }
    <?php } ?>

    <?php if ($use_render_form) { ?>
        return $this->renderForm('<?= $templates_path ?>/edit.html.twig', [
        '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
        'form' => $form,
        ]);
    <?php } else { ?>
        return $this->render('<?= $templates_path ?>/edit.html.twig', [
        '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
        'form' => $form->createView(),
        ]);
    <?php } ?>
    }

    <?= $generator->generateRouteForControllerMethod(sprintf('/{%s}', $entity_identifier), sprintf('%s_delete', $route_name), ['POST']) ?>
    #[RouteAction('<?= sprintf('%s_delete', $route_name) ?>', '<?= sprintf('%s_delete', $route_name) ?>', CONSTANTS::ROLE_SUPERADMIN)]
    <?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
        public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
    <?php } else { ?>
        public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, EntityManagerInterface $entityManager): Response
    <?php } ?>
    {
    <?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
        if ($this->isCsrfTokenValid('delete'.$<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>(), $request->request->get('_token'))) {
        $<?= $repository_var ?>->remove($<?= $entity_var_singular ?>, true);
        $this->addFlash('success', 'the entity has been successfully removed.');
        }
    <?php } else { ?>
        if ($this->isCsrfTokenValid('delete'.$<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>(), $request->request->get('_token'))) {
        $entityManager->remove($<?= $entity_var_singular ?>);
        $entityManager->flush();
        $this->addFlash('success', 'the entity has been successfully removed.');
        }
    <?php } ?>

    return $this->redirectToRoute('<?= $route_name ?>_index', ['_locale' => $request->getlocale()], Response::HTTP_SEE_OTHER);
    }
    }