<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Corrección: el seeder correcto es RolesSeeder (con 's')
        Artisan::call('db:seed', ['--class' => 'RolesSeeder']);
    }

    /** @test */
    public function puede_registrar_un_turista()
    {
        $response = $this->postJson('/api/auth/register-turista', [
            'name' => 'Turista Prueba',
            'email' => 'turista@prueba.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'usuario' => ['id', 'name', 'email', 'rol'],
                     'token'
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'turista@prueba.com'
        ]);
    }

    /** @test */
    public function no_puede_registrar_con_email_repetido()
    {
        User::factory()->create([
            'email' => 'turista@prueba.com'
        ]);

        $response = $this->postJson('/api/auth/register-turista', [
            'name' => 'Otro',
            'email' => 'turista@prueba.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        
        $response->assertStatus(422);
    }

    /** @test */
    public function puede_iniciar_sesion()
    {
        $user = User::factory()->create([
            'email' => 'turista@prueba.com',
            'password' => Hash::make('password123')
        ]);

        $user->assignRole('turista');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'turista@prueba.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'usuario' => ['id', 'name', 'email', 'rol'],
                     'token'
                 ]);
    }

    /** @test */
    public function no_puede_iniciar_sesion_con_credenciales_invalidas()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalido@prueba.com',
            'password' => 'incorrecta'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function puede_cerrar_sesion()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Sesión cerrada correctamente'
                 ]);
    }

    /** @test */
    public function puede_obtener_usuario_autenticado()
    {
        $user = User::factory()->create();
        $user->assignRole('turista');
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/auth/user');

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'name' => $user->name,
                     'email' => $user->email,
                     'rol' => 'turista'
                 ]);
    }
}
