@component('mail::message')
# Registration successful

<h2>Congratulations</h2>

<p>You have successfully been registered on the EXPRESS-LINK MICROFINANCE platform</p>
<p>You can access your back office to track your transactions with these login detail</p>
<p>Username : {{ $username }}</p>
<p>Password : {{ $password }}</p>

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Click here to login
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
