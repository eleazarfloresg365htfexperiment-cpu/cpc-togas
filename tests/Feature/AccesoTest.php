<?php

namespace Tests\Feature;

use App\Models\Alquiler;
use App\Models\User;
use Tests\Support\ConBaseDeDatosDePrueba;
use Tests\Support\CreaDatos;
use Tests\TestCase;

/**
 * Inicio de sesión, primer usuario, sección Usuarios y encargado del alquiler.
 */
class AccesoTest extends TestCase
{
    use ConBaseDeDatosDePrueba;
    use CreaDatos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function crearUsuario(array $datos = []): User
    {
        return User::create($datos + [
            'usuario' => 'eflores',
            'nombres' => 'Eleazar',
            'apellidos' => 'Flores',
            'password' => 'clave-segura',
            'activo' => true,
        ]);
    }

    public function test_sin_sesion_todo_lleva_al_login(): void
    {
        $this->crearUsuario();

        foreach (['/dashboard', '/productos', '/clientes', '/alquileres', '/usuarios', '/alquileres-web'] as $ruta) {
            $this->get($ruta)->assertRedirect(route('login'));
        }

        $this->post(route('clientes.store'), ['nombres' => 'X'])->assertRedirect(route('login'));
    }

    public function test_sistema_sin_usuarios_pide_crear_el_primero_y_entra(): void
    {
        $this->get(route('login'))->assertRedirect(route('configuracion-inicial'));
        $this->get(route('configuracion-inicial'))->assertOk()->assertSee('Crear el primer usuario');

        $this->post(route('configuracion-inicial.guardar'), [
            'nombres' => 'Eleazar',
            'apellidos' => 'Flores',
            'usuario' => 'EFlores ',
            'password' => 'clave-segura',
            'password_confirmation' => 'clave-segura',
        ])->assertRedirect(route('dashboard'));

        $usuario = User::sole();
        $this->assertSame('eflores', $usuario->usuario);
        $this->assertSame('Eleazar Flores', $usuario->nombre_completo);
        $this->assertAuthenticatedAs($usuario);

        // Ya con usuarios, la configuración inicial no deja crear otro.
        auth()->logout();
        $this->get(route('configuracion-inicial'))->assertRedirect(route('login'));
        $this->post(route('configuracion-inicial.guardar'), [
            'nombres' => 'Intruso', 'apellidos' => 'X', 'usuario' => 'intruso',
            'password' => 'clave-segura', 'password_confirmation' => 'clave-segura',
        ])->assertForbidden();
        $this->assertSame(1, User::count());
    }

    public function test_iniciar_y_cerrar_sesion(): void
    {
        $usuario = $this->crearUsuario();

        $this->from(route('login'))
            ->post(route('login.iniciar'), ['usuario' => 'eflores', 'password' => 'incorrecta'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['usuario' => 'Usuario o contraseña incorrectos.']);
        $this->assertGuest();

        $this->post(route('login.iniciar'), ['usuario' => 'eflores', 'password' => 'clave-segura'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);

        $this->get(route('dashboard'))->assertOk()->assertSee('Eleazar Flores')->assertSee('Cerrar sesión');

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_demasiados_intentos_bloquean_un_momento(): void
    {
        $this->crearUsuario();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.iniciar'), ['usuario' => 'eflores', 'password' => 'mal']);
        }

        $this->from(route('login'))
            ->post(route('login.iniciar'), ['usuario' => 'eflores', 'password' => 'clave-segura'])
            ->assertSessionHasErrors('usuario');
        $this->assertGuest();
    }

    public function test_usuario_desactivado_no_entra_y_pierde_la_sesion(): void
    {
        $otro = $this->crearUsuario();
        $inactivo = $this->crearUsuario(['usuario' => 'inactivo', 'activo' => false]);

        $this->post(route('login.iniciar'), ['usuario' => 'inactivo', 'password' => 'clave-segura'])
            ->assertSessionHasErrors('usuario');
        $this->assertGuest();

        // Si lo desactivan con la sesión abierta, sale en el siguiente clic.
        $inactivo->update(['activo' => true]);
        $this->actingAs($inactivo)->get(route('dashboard'))->assertOk();
        $inactivo->update(['activo' => false]);
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_seccion_usuarios_crea_edita_y_desactiva(): void
    {
        $yo = $this->crearUsuario();
        $this->actingAs($yo);

        $this->get(route('usuarios.index'))->assertOk()->assertSee('Eleazar Flores');

        $this->post(route('usuarios.store'), [
            'nombres' => 'María', 'apellidos' => 'López', 'usuario' => 'mlopez',
            'password' => 'otra-clave', 'password_confirmation' => 'otra-clave',
        ])->assertRedirect(route('usuarios.index'));

        $maria = User::where('usuario', 'mlopez')->firstOrFail();

        // Usuario repetido, con espacios o contraseñas distintas: no.
        $this->from(route('usuarios.create'))->post(route('usuarios.store'), [
            'nombres' => 'X', 'apellidos' => 'Y', 'usuario' => 'mlopez',
            'password' => 'otra-clave', 'password_confirmation' => 'otra-clave',
        ])->assertSessionHasErrors('usuario');
        $this->from(route('usuarios.create'))->post(route('usuarios.store'), [
            'nombres' => 'X', 'apellidos' => 'Y', 'usuario' => 'con espacio',
            'password' => 'otra-clave', 'password_confirmation' => 'distinta1',
        ])->assertSessionHasErrors(['usuario', 'password']);

        // Editar sin contraseña no la cambia; con contraseña sí.
        $this->put(route('usuarios.update', $maria), [
            'nombres' => 'María José', 'apellidos' => 'López', 'usuario' => 'mlopez',
        ])->assertRedirect(route('usuarios.index'));
        $this->assertSame('María José López', $maria->fresh()->nombre_completo);
        $this->assertTrue(auth()->validate(['usuario' => 'mlopez', 'password' => 'otra-clave']));

        $this->put(route('usuarios.update', $maria), [
            'nombres' => 'María José', 'apellidos' => 'López', 'usuario' => 'mlopez',
            'password' => 'nueva-clave', 'password_confirmation' => 'nueva-clave',
        ]);
        $this->assertTrue(auth()->validate(['usuario' => 'mlopez', 'password' => 'nueva-clave']));

        // Desactivar a otro sí; a uno mismo no.
        $this->patch(route('usuarios.desactivar', $maria))->assertSessionHas('success');
        $this->assertFalse($maria->fresh()->activo);
        $this->patch(route('usuarios.desactivar', $yo))->assertSessionHas('error');
        $this->assertTrue($yo->fresh()->activo);

        $this->patch(route('usuarios.reactivar', $maria));
        $this->assertTrue($maria->fresh()->activo);
    }

    public function test_el_encargado_se_llena_con_el_usuario_y_el_alquiler_guarda_quien_lo_registro(): void
    {
        $yo = $this->crearUsuario();
        $this->actingAs($yo);

        $this->get(route('alquileres.create'))
            ->assertOk()
            ->assertSee('id="representante_alquiler"', false)
            ->assertSee('value="Eleazar Flores"', false);

        $cliente = $this->cliente();
        $toga = $this->toga();
        $collarin = $this->collarin();

        // Se puede cambiar el encargado si atiende otra persona.
        $this->post(route('alquileres.store'), $this->formularioAlquiler($cliente, $toga, $collarin, ['cantidad' => 1], [
            'representante_alquiler' => 'Otra Persona',
        ]))->assertSessionHasNoErrors();

        $alquiler = Alquiler::sole();
        $this->assertSame('Otra Persona', $alquiler->representante_alquiler);
        $this->assertSame($yo->id, (int) $alquiler->usuario_id);
    }

    public function test_la_migracion_da_nombre_de_usuario_a_los_que_ya_existian(): void
    {
        \Illuminate\Support\Facades\DB::table('users')->insert([
            ['name' => 'Admin Viejo', 'email' => 'admin@cpc.com', 'password' => bcrypt('x'), 'usuario' => null, 'nombres' => null],
            ['name' => 'Otro', 'email' => 'admin@otro.com', 'password' => bcrypt('x'), 'usuario' => null, 'nombres' => null],
        ]);

        $migracion = require database_path('migrations/2026_10_07_120000_agregar_datos_de_acceso_a_users.php');
        $migracion->up();

        $this->assertSame(['admin', 'admin2'], User::orderBy('id')->pluck('usuario')->all());
        $this->assertSame('Admin Viejo', User::where('usuario', 'admin')->value('nombres'));
    }

    public function test_el_encargado_usa_primer_nombre_y_primer_apellido(): void
    {
        $casos = [
            ['Eleazar Josué', 'Flores García', 'Eleazar Flores'],
            ['María José', 'De León Pérez', 'María De León'],
            ['Juan', 'de la Cruz', 'Juan de la Cruz'],
            ['Ana', 'López', 'Ana López'],
        ];

        foreach ($casos as $i => [$nombres, $apellidos, $esperado]) {
            $usuario = $this->crearUsuario(['usuario' => 'u' . $i, 'nombres' => $nombres, 'apellidos' => $apellidos]);
            $this->assertSame($esperado, $usuario->nombre_corto);
        }

        $this->actingAs(User::where('usuario', 'u0')->first())
            ->get(route('alquileres.create'))
            ->assertSee('value="Eleazar Flores"', false);
    }
}
