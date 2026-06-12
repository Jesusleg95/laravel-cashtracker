<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Verificar que la pagina exista
it('shows the login screen', function(){
    $response = $this->get(route('login'));
    $response->assertOk();
});

// Verificar que un usuario pueda acceder con las credenciales correctas
it('logs in a verified user successfully', function(){
    User::factory()->create([
        'email' => 'correo@correo.com',
        'password' => bcrypt('P@ssw0rd'),
        'email_verified_at' => now()
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'correo@correo.com',
        'password' => 'P@ssw0rd'
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

// Verificar que el usuario no pueda iniciar sesion con credenciales incorrectas
it('do not log in a with invalid credentials', function(){
    User::factory()->create([
        'email' => 'correo@correo.com',
        'password' => bcrypt('P@ssw0rd')
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'correo@correo.com',
        'password' => 'P@ssw0rd!!'
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Credenciales Incorrectas');

    $this->assertGuest();
});

// Verificar que un usuario no verificado pueda acceder al dashboard
it('prevents unverified user from accessing dashboard', function(){
     User::factory()->unverified()->create([
        'email' => 'correo@correo.com',
        'password' => bcrypt('P@ssw0rd')
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'correo@correo.com',
        'password' => 'P@ssw0rd'
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $dashboardResponse = $this->get(route('dashboard'));
    $dashboardResponse->assertRedirect(route('verification.notice'));
});

it('does not allow access to dashboard if email is not verified', function(){
    $user = User::factory()->create([
        'email_verified_at' => null
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertRedirect(route('verification.notice'));
});

// Verificar que un usuario verificado si entra al dashboard
it('allow access to dashboard if email is verified', function(){
    $user = User::factory()->create([
        'email_verified_at' => now()
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();
});

// Verificar que un usuario sin cuenta no pueda acceder
it('fails login if user does not exist', function(){
    $response = $this->from(route('login'))->post(route('login.store'),[
        'email' => 'correo@noexiste.com',
        'password' => 'P@ssw0rd'
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => 'E-mail no registrado'
    ]);

    $this->assertGuest();
});