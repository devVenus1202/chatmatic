@component('mail::message')
# You have been set as a page admin

You have been set as admin for the page {{ $page_name }}

[Chatmatic login]({!! env('APP_UI_URL') !!})


@endcomponent