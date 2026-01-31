@component('mail::message')
# رمز التحقق الخاص بك

لاتمام عملية استعادة كلمة المرور، يرجى استخدام الرمز التالي:

@component('mail::panel')
{{ $code }}
@endcomponent

الرمز صالح لمدة قصيرة.

شكراً,<br>
{{ config('app.name') }}
@endcomponent
