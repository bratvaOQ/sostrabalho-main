<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Notification;
use Carbon\Carbon;

class ExpirationService
{
    /**
     * Verificar e marcar anúncios expirados
     */
    public static function checkExpiredListings()
    {
        $expiredJobs = Job::where('expires_at', '<=', now())
            ->where('is_expired', false)
            ->get();

        foreach ($expiredJobs as $job) {
            $job->markAsExpired();

            // Criar notificação
            Notification::create([
                'user_id' => $job->user_id,
                'type' => 'job_expired',
                'message' => "Seu anúncio '{$job->title}' expirou",
                'icon' => 'clock',
                'related_job_id' => $job->id,
                'read' => false,
            ]);
        }

        return $expiredJobs->count();
    }

    /**
     * Avisar antes de expirar (3 dias)
     */
    public static function checkSoonToExpire()
    {
        $soonToExpire = Job::whereBetween('expires_at', [
            now(),
            now()->addDays(3)
        ])
            ->where('is_expired', false)
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', 'job_expiring_soon');
            })
            ->get();

        foreach ($soonToExpire as $job) {
            Notification::create([
                'user_id' => $job->user_id,
                'type' => 'job_expiring_soon',
                'message' => "Seu anúncio '{$job->title}' vence em " .
                    $job->expires_at->diffInDays(now()) . " dias",
                'icon' => 'alert-circle',
                'related_job_id' => $job->id,
                'read' => false,
            ]);
        }

        return $soonToExpire->count();
    }
}
