<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleKilometrageReport;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\UnfilledKilometrageReportEntryNotification;

class UnfilledKilometrageReportEntryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_toArray_stores_notification_correctly()
    {
        // Create a user, vehicle, and kilometrage report
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();
        
        // Define a missing date (relevant for the notification)
        $missingDate = now()->subMonth()->startOfMonth()->format('Y-m-d');

        // Make sure there are no reports or dates might coincide making the test fail
        VehicleKilometrageReport::where('vehicle_id', $vehicle->id)->delete();

        // Send notification
        $notification = new UnfilledKilometrageReportEntryNotification($vehicle, $missingDate);
        $user->notify($notification);

        // Check if the notification is stored in the database
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'related_entity_id' => $vehicle->id,
            'related_entity_type' => Vehicle::class,
            'type' => 'Relatório de Kilometragem'
        ]);
    }

    public function test_toMail_sends_correct_notification()
    {
        // Arrange: Create a vehicle and accessory
        $vehicle = Vehicle::factory()->create();
        $missingDate = now()->subMonth()->startOfMonth()->format('Y-m-d');


        // Create an instance of the notification
        $notification = new UnfilledKilometrageReportEntryNotification($vehicle, $missingDate);

        // Simulate a notifiable (could be a user or an anonymous notifiable)
        $notifiable = new AnonymousNotifiable();

        // Act: Call the toMail method
        $mailMessage = $notification->toMail($notifiable);

        // Assert: Verify that the mail message is properly structured
        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $this->assertStringContainsString('Entrada de relatório de kilometragem em falta.', $mailMessage->introLines[0]);
        $this->assertStringContainsString(route('vehicles.kilometrageReports', ['vehicle' => $vehicle->id]), $mailMessage->actionUrl);
        $this->assertStringContainsString('No mês passado, uma entrada do relatório de kilometragem não foi preenchida na data ' . $missingDate . ' para o veículo ' . $vehicle->id . '.', $mailMessage->outroLines[0]);
    }
}
