<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Datos de cancelación y total de daños en el alquiler
        |--------------------------------------------------------------------------
        */
        Schema::table('alquileres', function (Blueprint $table) {
            if (!Schema::hasColumn('alquileres', 'fecha_cancelacion')) {
                $table->dateTime('fecha_cancelacion')->nullable()->after('estado');
            }

            if (!Schema::hasColumn('alquileres', 'motivo_cancelacion')) {
                $table->text('motivo_cancelacion')->nullable()->after('fecha_cancelacion');
            }

            if (!Schema::hasColumn('alquileres', 'monto_danos')) {
                $table->decimal('monto_danos', 10, 2)->default(0)->after('monto_mora');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Historial de cambios del alquiler
        |--------------------------------------------------------------------------
        | Guarda cada edición, cancelación y registro/eliminación de daños,
        | con el valor anterior, el nuevo y el motivo.
        */
        Schema::create('alquiler_historial', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alquiler_id')
                ->constrained('alquileres')
                ->cascadeOnDelete();

            $table->string('accion', 30); // EDICION, CANCELACION, DANO, DANO_ELIMINADO
            $table->string('campo', 60)->nullable();
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->text('motivo')->nullable();
            $table->string('responsable', 255)->nullable();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['alquiler_id', 'created_at']);
        });

        /*
        |--------------------------------------------------------------------------
        | Daños y extravíos de un alquiler devuelto
        |--------------------------------------------------------------------------
        */
        Schema::create('alquiler_danos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alquiler_id')
                ->constrained('alquileres')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete();

            $table->enum('tipo', ['DANO', 'EXTRAVIO']);
            $table->unsignedInteger('cantidad');
            $table->decimal('monto', 10, 2)->default(0);
            $table->text('descripcion')->nullable();
            $table->string('responsable', 255)->nullable();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alquiler_danos');
        Schema::dropIfExists('alquiler_historial');

        Schema::table('alquileres', function (Blueprint $table) {
            foreach (['fecha_cancelacion', 'motivo_cancelacion', 'monto_danos'] as $columna) {
                if (Schema::hasColumn('alquileres', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
