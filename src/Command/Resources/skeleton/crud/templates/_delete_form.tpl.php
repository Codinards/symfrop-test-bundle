{{ create_symfrop_form(
    '<?= $route_name ?>_delete', __u('Delete'),
    {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>, _locale: app.request.locale ?? app.request.defaultLocale },
    { class: "btn btn-danger", onSubmit: __u('Are you sure you want to delete this item?'), _token: csrf_token('delete' ~ <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>) }
)}}