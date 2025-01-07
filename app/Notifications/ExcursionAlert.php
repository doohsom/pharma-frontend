<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExcursionAlert extends Notification
{
    use Queueable;

    private $batchId;
    private $sensorId;
    private $reading;
    private $orderDetails;

    public function __construct($batchId, $sensorId, $reading, $orderDetails)
    {
        $this->batchId = $batchId;
        $this->sensorId = $sensorId;
        $this->reading = $reading;
        $this->orderDetails = $orderDetails;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Temperature Excursion Alert - Batch #{$this->batchId}")
            ->line("A temperature excursion has been detected in batch #{$this->batchId}")
            ->line("Sensor ID: {$this->sensorId}")
            ->line("Temperature: {$this->reading['value']}°C")
            ->line("Time: " . \Carbon\Carbon::parse($this->reading['timestamp'])->format('Y-m-d H:i:s'))
            ->line("Order ID: {$this->orderDetails['order_id']}")
            ->line("Product: {$this->orderDetails['product_name']}")
            ->action('View Details', url("/batches/{$this->batchId}/sensors/{$this->sensorId}"))
            ->line('Please take immediate action.');
    }
}