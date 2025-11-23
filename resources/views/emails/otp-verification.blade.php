<x-mail::message>
# Email Verification Required

Hello! Thank you for registering with our e-commerce platform.

To complete your registration and access your account, please use the following One-Time Password (OTP) code.

<div style="text-align: center; margin: 30px 0; padding: 20px; background-color: #f8f8f8; border: 1px solid #ddd; border-radius: 4px;">
    <h2 style="font-size: 28px; color: #333; margin: 0;">{{ $otpCode }}</h2>
</div>

This code is valid for 5 minutes. Please enter it on the verification screen to proceed.

If you did not initiate this request, you can safely ignore this email.

{{-- <x-mail::button :url="route('/')">
Go to Website (Optional)
</x-mail::button> --}}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>