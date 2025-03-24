<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Expired extends Notification
{
    use Queueable;
    private $entree;
    public $type_name = "expired";
    public $type_id;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($entree)
    {
        $this->entree = $entree;
        $this->type_id = $entree->id; 
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title'=>'Lot N°:'.$this->entree->n_lot.' expiré',
            'body'=>'Produit: '.$this->entree->produit->label.', Quantité:'.$this->entree->qte
        ];
    }
}
