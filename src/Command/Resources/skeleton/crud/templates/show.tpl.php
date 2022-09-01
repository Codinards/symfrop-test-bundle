<?= $helper->getHeadPrintCode("{{ __t('" .  $entity_class_name . "') }} : " . $entity_class_name) ?>

{% block header %}
<h1>{{ __t('<?= $entity_class_name ?>') }} : {{ <?= $entity_class_name ?> }}</h1>
{% endblock  %}

{% block body %}
{{ create_symfrop_link('<?= $route_name ?>_index', __('back to list'), {_locale: app.request.locale ?? app.request.defaultLocale}, {class: "btn btn-info"}) }}
{{ create_symfrop_link('<?= $route_name ?>_edit', __('edit'),  {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>, _locale: app.request.locale ?? app.request.defaultLocale}, {class:"btn btn-success"}) }}
<hr>
<table class="table">
    <tbody>
        <?php foreach ($entity_fields as $field) : ?>
            <tr>
                <th>{{ __u('<?= ucfirst($field['fieldName']) ?>') }}</th>
                <td>{{ <?= $helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

{{ include('<?= $templates_path ?>/_delete_form.html.twig') }}
{% endblock %}