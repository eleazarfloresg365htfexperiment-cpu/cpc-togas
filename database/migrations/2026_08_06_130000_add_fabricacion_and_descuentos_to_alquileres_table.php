<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->decimal('descuento_manual', 10, 2)->default(0)->after('descuento');
            $table->decimal('descuento_toga', 10, 2)->default(0)->after('descuento_manual');
        });

        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE alquileres MODIFY COLUMN estado ENUM('RESERVADO','EN_FABRICACION','LISTO_PARA_ENTREGA','ENTREGADO','DEVUELTO','CANCELADO') NOT NULL DEFAULT 'RESERVADO'");
        }
    }

    public function down(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn(['descuento_manual', 'descuento_toga']);
        });

        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE alquileres MODIFY COLUMN estado ENUM('RESERVADO','ENTREGADO','DEVUELTO','CANCELADO') NOT NULL DEFAULT 'RESERVADO'");
        }
    }
};
