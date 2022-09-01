<?= $helper->getHeadPrintCode("{{ __t('" .  $entity_class_name . "') }} {{ __u('index') }}"); ?>

{% block header %}
<h1>{{ __t('<?= $entity_class_name ?>') }} {{ __('index') }}</h1>
{% endblock  %}

{% block body %}

<a href="{{ path('<?= $route_name ?>_new', {_locale: app.request.locale ?? app.request.defaultLocale}) }}" class="btn btn-primary">{{ __('Create new') }}</a>
<hr>
<table class="table">
    <thead>
        <tr>
            <?php foreach ($entity_fields as $field) : ?>
                <th>{{ __u('<?= ucfirst($field['fieldName']) ?>') }}</th>
            <?php endforeach; ?>
            <th>{{ __('actions') }}</th>
        </tr>
    </thead>
    <tbody>
        {% for <?= $entity_twig_var_singular ?> in <?= $entity_twig_var_plural ?> %}
        <tr>
            <?php foreach ($entity_fields as $field) : ?>
                <td>{{ <?= $helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}</td>
            <?php endforeach; ?>
            <td>
                <a href="{{ path('<?= $route_name ?>_show', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>, _locale: app.request.locale ?? app.request.defaultLocale}) }}" class="btn btn-info">{{ __('show') }}</a>
                <a href="{{ path('<?= $route_name ?>_edit', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>, _locale: app.request.locale ?? app.request.defaultLocale}) }}" class="btn btn-success">{{ __('edit') }}</a>
            </td>
        </tr>
        {% else %}
        <tr>
            <td colspan="<?= (count($entity_fields) + 1) ?>">{{ __('no records found') }}</td>
        </tr>
        {% endfor %}
    </tbody>
</table>
{% endblock %}