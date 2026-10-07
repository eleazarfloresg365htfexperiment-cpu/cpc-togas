<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Datos para iniciar sesión: nombre de usuario, nombres y apellidos (para
 * "Representante o encargado del alquiler") y si el usuario está activo.
 * El correo deja de ser obligatorio.
 *
 * Solo agrega columnas. Si ya había usuarios, se les arma un nombre de
 * usuario a partir del correo o del nombre para que puedan entrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'usuario')) {
                $table->string('usuario', 50)->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('users', 'nombres')) {
                $table->string('nombres', 100)->nullable()->after('usuario');
            }

            if (!Schema::hasColumn('users', 'apellidos')) {
                $table->string('apellidos', 100)->nullable()->after('nombres');
            }

            if (!Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true)->after('password');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // Usuarios que ya existían: se les asigna un nombre de usuario único.
        foreach (DB::table('users')->whereNull('usuario')->orderBy('id')->get() as $usuario) {
            $base = Str::slug(Str::before((string) ($usuario->email ?: $usuario->name), '@'), '') ?: 'usuario';
            $propuesto = $base;
            $n = 1;

            while (DB::table('users')->where('usuario', $propuesto)->exists()) {
                $propuesto = $base . (++$n);
            }

            DB::table('users')->where('id', $usuario->id)->update([
                'usuario' => $propuesto,
                'nombres' => $usuario->nombres ?? $usuario->name,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['usuario']);
            $table->dropColumn(['usuario', 'nombres', 'apellidos', 'activo']);
        });
    }
};
