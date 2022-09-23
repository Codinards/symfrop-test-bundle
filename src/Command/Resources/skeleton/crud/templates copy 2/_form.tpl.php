{{ form_start(form) }}
{{ form_widget(form) }}
<div class="form-group text-right">
    <button class="btn btn-success">{{ __u(button_label|default('Save')) }}</button>
</div>
{{ form_end(form) }}