<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE productos 
            MODIFY tipo_producto ENUM('TOGA', 'BIRRETE', 'COLLARIN', 'BORLA') 
            NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE productos 
            MODIFY tipo_producto ENUM('TOGA', 'BIRRETE', 'COLLARIN') 
            NOT NULL
        ");
    }
};