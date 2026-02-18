<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\PreventiveTaskAutomationService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('gmao:generate-preventive-tasks', function (PreventiveTaskAutomationService $service) {
    try {
        $result = $service->generateDueTasks();
        $this->info("Generated tasks: {$result['tasks_created']} | Updated plans: {$result['plans_updated']}");
    } catch (Throwable $e) {
        report($e);
        $this->error('Preventive generation failed.');
    }
})->purpose('Generate preventive tasks from due preventive plans');

Artisan::command('gmao:send-preventive-reminders', function (PreventiveTaskAutomationService $service) {
    try {
        $sent = $service->sendUpcomingReminders();
        $this->info("Sent reminders: {$sent}");
    } catch (Throwable $e) {
        report($e);
        $this->error('Preventive reminder dispatch failed.');
    }
})->purpose('Send reminders for upcoming preventive tasks');

