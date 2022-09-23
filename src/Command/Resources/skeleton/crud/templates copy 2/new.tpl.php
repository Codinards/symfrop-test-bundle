<?= $helper->getHeadPrintCode("{{ __u('Create new') }} {{ __t('" .  $entity_class_name . "') }}") ?>

{% block header %}
<h1>{{ __u('Create new') }} {{ __t('<?= $entity_class_name ?>') }} </h1>
{% endblock  %}

{% block body %}
{{ create_symfrop_link('<?= $route_name ?>_index', __('back to list'), {_locale: app.request.locale ?? app.request.defaultLocale}, {class: "btn btn-info"}) }}
<hr>
{{ include('<?= $templates_path ?>/_form.html.twig') }}

{% endblock %}