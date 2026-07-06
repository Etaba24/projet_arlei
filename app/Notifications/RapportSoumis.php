<?php

namespace App\Notifications;

use App\Models\Rapport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RapportSoumis extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Rapport $rapport)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'rapport',
            'title' => 'Nouveau rapport reçu',
            'message' => sprintf('Le rapport "%s" a été soumis par %s.', $this->rapport->titre, $this->rapport->user->name),
            'url' => route('rapports.show', $this->rapport),
        ];
    }
}
