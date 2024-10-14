<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

//TODO: CREATE MORE NOTIFICATION TYPES
class CustomDbChannel
{
  	public function send($notifiable, Notification $notification)
  	{
		$fields = $notification->toArray($notifiable);

		// Prepare notification payload
		$payload = [
			'user_id' => $notifiable->id,
			'related_entity_type' => $fields['related_entity_type'],
			'related_entity_id' => $fields['related_entity_id'],
			'type' => $fields['type'],
			'title' => $fields['title'],
			'message' => $fields['message'],
			'is_read' => $fields['is_read'],
		];

		// Create a new notification instance and save it
		return $notifiable->routeNotificationFor('database', $notification)->create($payload);
	}
}