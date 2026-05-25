<?php

namespace App\Providers;

use App\Mail\MailtrapSandboxTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar HTTPS en producción (Railway/PaaS terminan SSL en su proxy
        // y nos pasan HTTP, lo que rompe assets con "mixed content").
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Registrar transport custom de Mailtrap Sandbox API.
        // Se usa cuando MAIL_MAILER=mailtrap_sandbox.
        // Necesario porque Railway bloquea SMTP outbound; este transport va por HTTPS.
        Mail::extend('mailtrap_sandbox', function (array $config) {
            return new MailtrapSandboxTransport(
                $config['token'] ?? '',
                $config['inbox_id'] ?? ''
            );
        });
    }
}
