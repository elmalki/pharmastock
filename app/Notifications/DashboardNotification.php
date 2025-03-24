<?php

namespace App\Notifications;

use App\Models\Produit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DashboardNotification extends Notification
{
    use Queueable;


    public function __construct(private Produit $produit)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->produit->label.' est en rupture de stock',
            'message' => "Stock critique est de ".$this->produit->limit_command." restants ".$this->produit->qte,
        ];
    }
}
