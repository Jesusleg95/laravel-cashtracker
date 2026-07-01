<?php

use App\Models\Budget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows the owner to update a budget', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $budget = Budget::factory()->for($user)->create([
        'name' => 'Presupuesto Original',
        'amount' => 1000,
        'type' => 'general'
    ]);

    $response = $this->actingAs($user)->put(route('budgets.update', $budget), [
        'name' => 'Presupuesto Actualizado',
        'amount' => 1500,
        'type' => 'goal'
    ]); 

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success', 'Presupuesto actualizado correctamente');

    $this->assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'name' => 'Presupuesto Actualizado',
        'amount' => 1500,
        'type' => 'goal',
        'user_id' => $user->id
    ]);

});

it('validates required fields when updating a budget', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $budget = Budget::factory()->for($user)->create();

    $response = $this->actingAs($user)->from(route('budgets.edit', $budget))->put(
        route('budgets.update', $budget),[
            'name' => '',
            'amount' => '',
            'type' => ''
        ]
    );

    $response->assertRedirect(route('budgets.edit', $budget));
    $response->assertSessionHasErrors([
        'name',
        'amount',
        'type'
    ]);

});

it('validates amount must be greater than zero when updating a budget', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $budget = Budget::factory()->for($user)->create();

    $response = $this->actingAs($user)->from(route('budgets.edit', $budget))->put(
        route('budgets.update', $budget),[
            'name' => 'Vacaciones',
            'amount' => 0,
            'type' => 'general'
        ]
    );

    $response->assertRedirect(route('budgets.edit', $budget));
    $response->assertSessionHasErrors([
        'amount',
    ]);


});

it('validates type must be valid when updating a budget', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $budget = Budget::factory()->for($user)->create();

    $response = $this->actingAs($user)->from(route('budgets.edit', $budget))->put(
        route('budgets.update', $budget),[
            'name' => 'Vacaciones',
            'amount' => 1000,
            'type' => 'not_valid'
        ]
    );

    $response->assertRedirect(route('budgets.edit', $budget));
    $response->assertSessionHasErrors([
        'type',
    ]);

});

it('does not allow guests to update budgets', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $budget = Budget::factory()->for($user)->create();

    $response = $this->put(route('budgets.update', $budget), [
        'name' => 'Presupuesto',
        'amount' => 1500,
        'type' => 'general'
    ]);

    $response->assertRedirect(route('login'));

});

it('does not allow other users to update budgets', function () {
    $owner = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $budget = Budget::factory()->for($owner)->create([
        'name' => 'Presupuesto Original'
    ]);
    
    $response = $this->actingAs($otherUser)->put(route('budgets.update', $budget), [
        'name' => 'Hackeado!!!!!',
        'amount' => 999999,
        'type' => 'goal'
    ]);

    $response->assertForbidden();
    $this->assertDataBaseHas('budgets', [
        'id' => $budget->id,
        'name' => 'Presupuesto Original'
    ]);

    
});
