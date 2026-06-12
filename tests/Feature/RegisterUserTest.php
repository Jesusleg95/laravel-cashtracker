<?php

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

// Crea las migraciones y ejecuta la BD de prueba
uses(RefreshDatabase::class);

it('shows the registration screen', function(){
    $response = $this->get(route('register'));

    $response->assertOk();
    $response->assertStatus(200);
    $response->assertSee('Crear Cuenta');

});

// Testing al formulario de registro y redireccion
it('registers a new user as unverified and dispatches the registered event', function(){
    Event::fake();

    // Ingresa los datos en el formulario
    $response = $this->post(route('register.store'), [
        'name' => 'Jesus',
        'email' => 'correo@correo.com',
        'password' => 'P@ssw0rd',
        'password_confirmation' => 'P@ssw0rd'
    ]);

    // Redirige al usuario una vez registrado
    $response->assertRedirect(route('verification.notice'));

    // Verificar que el usuario se haya registrado correctamente
    $user = User::where('email', 'correo@correo.com')->first();

    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Jesus');
    expect($user->email)->toBe('correo@correo.com');
    expect($user->hasVerifiedEmail())->toBeFalse;

    Event::assertDispatched(Registered::class);
});

// Validacion de los errores en caso de tener campos vacios
it('should validate required fields when the request body is empty', function() {
    $response = $this->post(route('register.store'), []);

    $response->assertSessionHasErrors([
        'name',
        'email',
        'password'
    ]);
});

// Previene que se registren datos duplicados(email)
it('prevents duplicate email addresses', function(){
    
    // factory nos ayuda a crear datos aleatorios, 
    // a excepcion del que pidamos, en este ejemplo email
    User::factory()->create([
        'email' => 'correo@correo.com'
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Jesus',
        'email' => 'correo@correo.com',
        'password' => 'P@ssw0rd',
        'password_confirmation' => 'P@ssw0rd'
    ]);

    $response->assertRedirect();

    $response->assertSessionHasErrors([
        'email' => 'Este E-mail ya está registrado'
    ]);  

});

it('sends the verification email notification after registration', function(){
    Notification::fake();

    // Ingresa los datos en el formulario
    $response = $this->post(route('register.store'), [
        'name' => 'Jesus',
        'email' => 'correo@correo.com',
        'password' => 'P@ssw0rd',
        'password_confirmation' => 'P@ssw0rd'
    ]);

    // Verificar que el usuario se haya registrado correctamente
    $user = User::where('email', 'correo@correo.com')->first();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('verifies the user email from signed vertification link', function(){

    $user = User::factory()->unverified()->create();
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]
    );

    $response = $this->actingAs($user)->get($verificationUrl);
    $response->assertRedirect(route('dashboard'));

    expect($user->hasVerifiedEmail())->toBeTrue();
});

it('does not allow an unverified user to access the dashboard', function(){
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertRedirect(route('verification.notice'));

});

it('allows a verified user to access the dashboard', function(){
    $user = User::factory()->create([
        'email_verified_at' => now()
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();
});