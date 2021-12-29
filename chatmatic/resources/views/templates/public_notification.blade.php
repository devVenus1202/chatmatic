@component('mail::message')
# A template is wanted to be pushed as public.

Template owner: {{ $template_owner }} - Template name {{ $template_name }}


[Click to check]({!! env('APP_UI_URL')template/{{ $template_uid }} !!})


@endcomponent

