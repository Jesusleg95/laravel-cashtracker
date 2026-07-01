<?php

use App\Models\Budget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows the owner to view the edit budget form', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $budget = Budget::factory()->for($user)->create([
        'name' => 'Vacaciones',
        'amount' => 1000,
        'type' => 'general'
    ]);

    $response = $this->actingAs($user)->get(route('budgets.edit', $budget));
    $response->assertOk();
    $response->assertSee('Vacaciones');
});

it('does not allow guests to view the edit budget form', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $budget = Budget::factory()->for($user)->create();
    $response = $this->get(route('budgets.edit', $budget));
    $response->assertRedirect(route('login'));

});

it('does not allow other users to view the edit budget form', function () {
    $owner = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $budget = Budget::factory()->for($owner)->create();
    $response = $this->actingAs($otherUser)->get(route('budgets.edit', $budget));
    $response->assertForbidden();
    
});
