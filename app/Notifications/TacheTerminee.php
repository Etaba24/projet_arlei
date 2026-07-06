<?php

namespace App\Notifications;

use App\Models\PhaseProduction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TacheTerminee extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PhaseProduction $phase)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'tache',
            'title' => 'Tâche effectuée',
            'message' => sprintf('La tâche "%s" pour l’OP %s a été marquée comme terminée.', $this->phase->transformation->designation, $this->phase->ordreProduction->code),
            'url' => route('ordre-productions.show', $this->phase->ordreProduction),
        ];
    }
}
