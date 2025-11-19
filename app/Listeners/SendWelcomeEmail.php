<?php

namespace App\Listeners;

use App\Events\AccountProvisioned;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail
{
    public function handle(AccountProvisioned $event): void
    {
        $panelAccount = $event->panelAccount;
        $subscription = $panelAccount->subscription;
        $customer = $subscription->customer;

        // TODO: Implementasi welcome email dengan credentials
        Log::info("Sending welcome email to: {$customer->email}");

        // Contoh:
        // Mail::to($customer->email)->send(new WelcomeMail($panelAccount));
    }
}
