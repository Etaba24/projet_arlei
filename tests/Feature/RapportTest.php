<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RapportTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_submit_report_and_admin_receives_notification(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $operator = User::factory()->create([
            'name' => 'Operateur Test',
            'email' => 'operateur@example.com',
            'role' => 'operateur',
        ]);

        $response = $this->actingAs($operator)->post('/rapports', [
            'titre' => 'Incident machine',
            'contenu' => 'La machine est en panne depuis ce matin.',
        ]);

        $response->assertRedirect('/rapports');
        $this->assertDatabaseHas('rapports', [
            'user_id' => $operator->id,
            'titre' => 'Incident machine',
        ]);

        $this->assertCount(1, $admin->fresh()->notifications);
    }
}
