<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->enum('estado_pago', [
                'PENDIENTE',
                'PARCIAL',
                'PAGADO',
            ])->default('PENDIENTE')->after('estado');
        });

        DB::table('alquileres')
            ->where('saldo_pendiente', '<=', 0)
            ->update(['estado_pago' => 'PAGADO']);

        DB::table('alquileres')
            ->where('saldo_pendiente', '>', 0)
            ->whereColumn('saldo_pendiente', '<', 'total')
            ->update(['estado_pago' => 'PARCIAL']);

        DB::table('alquileres')
            ->where('saldo_pendiente', '>', 0)
            ->whereColumn('saldo_pendiente', '>=', 'total')
            ->update(['estado_pago' => 'PENDIENTE']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn('estado_pago');
        });
    }
};