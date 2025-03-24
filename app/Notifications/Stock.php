<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Stock extends Notification
{
    use Queueable;
    private $produit;
    public $type_name = "stock";
    public $type_id;
    /**
     * Create a new notification instance.
     *
     * @return void
     *
     */
    public function __construct($produit)
    {
        $this->produit = $produit;
        $this->type_id = $produit->id;
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
           'title'=>$this->produit->label.' est en rupture de stock','body'=>'le stock acctuel est :'.$this->produit->qte,
        ];
    }
}
