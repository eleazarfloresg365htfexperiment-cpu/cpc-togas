<?php

namespace Tests\Support;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Igual que RefreshDatabase, pero construye el esquema con las migraciones
 * adaptadas para SQLite (ver EsquemaSqlite).
 */
trait ConBaseDeDatosDePrueba
{
    use RefreshDatabase;

    protected function migrateFreshUsing(): array
    {
        return [
            '--drop-views' => $this->shouldDropViews(),
            '--drop-types' => $this->shouldDropTypes(),
            '--path' => EsquemaSqlite::rutaMigraciones(),
            '--realpath' => true,
        ];
    }
}
