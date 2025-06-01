<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserTest extends TestCase
{
    /** @test */
    public function puede_hashear_contrasena_correctamente()
    {
        $password = '12345678';
        $hashed = Hash::make($password);

        $this->assertTrue(Hash::check($password, $hashed));
    }

    /** @test */
    public function el_modelo_usuario_tiene_el_rol_asignado()
    {
        $user = new User();
        $user->setAttribute('name', 'Test');
        $user->setAttribute('email', 'test@example.com');
        $user->setAttribute('password', bcrypt('12345678'));

        // Simular rol asignado (mock)
        $user->setRelation('roles', collect([
            (object)['name' => 'emprendedor']
        ]));

        $this->assertEquals('emprendedor', $user->roles->first()->name);
    }

    /** @test */
    public function el_nombre_del_usuario_es_un_string()
    {
        $user = new User(['name' => 'Juan Tester']);

        $this->assertIsString($user->name);
    }
}
