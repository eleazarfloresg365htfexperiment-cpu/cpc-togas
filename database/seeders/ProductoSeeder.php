<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\ProductoToga;
use App\Models\ProductoBirrete;
use App\Models\ProductoCollarin;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | TOGAS
        |--------------------------------------------------------------------------
        */

        $togaS = Producto::create([
            'codigo' => 'TOGA-S-NEGRA',
            'nombre' => 'Toga negra talla S',
            'tipo_producto' => 'TOGA',
            'descripcion' => 'Toga negra para graduación, talla S.',
            'precio_alquiler' => 75.00,
            'stock_total' => 10,
            'stock_disponible' => 10,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);

        ProductoToga::create([
            'producto_id' => $togaS->id,
            'talla' => 'S',
            'color' => 'Negro',
            'observaciones' => 'Toga pequeña para estudiantes de baja estatura.',
        ]);

        $togaM = Producto::create([
            'codigo' => 'TOGA-M-NEGRA',
            'nombre' => 'Toga negra talla M',
            'tipo_producto' => 'TOGA',
            'descripcion' => 'Toga negra para graduación, talla M.',
            'precio_alquiler' => 75.00,
            'stock_total' => 15,
            'stock_disponible' => 15,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);

        ProductoToga::create([
            'producto_id' => $togaM->id,
            'talla' => 'M',
            'color' => 'Negro',
            'observaciones' => 'Toga estándar para estudiantes.',
        ]);

        $togaL = Producto::create([
            'codigo' => 'TOGA-L-NEGRA',
            'nombre' => 'Toga negra talla L',
            'tipo_producto' => 'TOGA',
            'descripcion' => 'Toga negra para graduación, talla L.',
            'precio_alquiler' => 75.00,
            'stock_total' => 12,
            'stock_disponible' => 12,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);

        ProductoToga::create([
            'producto_id' => $togaL->id,
            'talla' => 'L',
            'color' => 'Negro',
            'observaciones' => 'Toga grande para estudiantes altos.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | BIRRETES
        |--------------------------------------------------------------------------
        */

        $birreteEstandar = Producto::create([
            'codigo' => 'BIR-EST-NEGRO',
            'nombre' => 'Birrete estándar negro',
            'tipo_producto' => 'BIRRETE',
            'descripcion' => 'Birrete estándar color negro.',
            'precio_alquiler' => 25.00,
            'stock_total' => 20,
            'stock_disponible' => 20,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);

        ProductoBirrete::create([
            'producto_id' => $birreteEstandar->id,
            'tipo_birrete' => 'ESTANDAR',
            'color' => 'Negro',
            'carrera' => null,
            'tiene_borlas_extra' => false,
            'descripcion_borlas_extra' => null,
        ]);

        $birreteNormal = Producto::create([
            'codigo' => 'BIR-NORMAL',
            'nombre' => 'Birrete normal',
            'tipo_producto' => 'BIRRETE',
            'descripcion' => 'Birrete normal para graduación.',
            'precio_alquiler' => 25.00,
            'stock_total' => 15,
            'stock_disponible' => 15,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);

        ProductoBirrete::create([
            'producto_id' => $birreteNormal->id,
            'tipo_birrete' => 'NORMAL',
            'color' => null,
            'carrera' => null,
            'tiene_borlas_extra' => false,
            'descripcion_borlas_extra' => null,
        ]);

        $birreteDerecho = Producto::create([
            'codigo' => 'BIR-UNI-DERECHO',
            'nombre' => 'Birrete universitario Derecho',
            'tipo_producto' => 'BIRRETE',
            'descripcion' => 'Birrete universitario para carrera de Derecho.',
            'precio_alquiler' => 30.00,
            'stock_total' => 8,
            'stock_disponible' => 8,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);

        ProductoBirrete::create([
            'producto_id' => $birreteDerecho->id,
            'tipo_birrete' => 'UNIVERSITARIO',
            'color' => null,
            'carrera' => 'DERECHO',
            'tiene_borlas_extra' => true,
            'descripcion_borlas_extra' => 'Incluye borla universitaria adicional para Derecho.',
        ]);

        $birreteAdmin = Producto::create([
            'codigo' => 'BIR-UNI-ADMIN',
            'nombre' => 'Birrete universitario Administración',
            'tipo_producto' => 'BIRRETE',
            'descripcion' => 'Birrete universitario para carrera de Administración.',
            'precio_alquiler' => 30.00,
            'stock_total' => 8,
            'stock_disponible' => 8,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);

        ProductoBirrete::create([
            'producto_id' => $birreteAdmin->id,
            'tipo_birrete' => 'UNIVERSITARIO',
            'color' => null,
            'carrera' => 'ADMINISTRACION',
            'tiene_borlas_extra' => true,
            'descripcion_borlas_extra' => 'Incluye borla universitaria adicional para Administración.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | COLLARINES
        |--------------------------------------------------------------------------
        */

        $collarinDoradoPequeno = Producto::create([
            'codigo' => 'COL-NOR-DOR-PEQ',
            'nombre' => 'Collarín normal dorado pequeño',
            'tipo_producto' => 'COLLARIN',
            'descripcion' => 'Collarín normal color dorado, tamaño pequeño.',
            'precio_alquiler' => 20.00,
            'stock_total' => 10,
            'stock_disponible' => 10,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);

        ProductoCollarin::create([
            'producto_id' => $collarinDoradoPequeno->id,
            'tipo_collarin' => 'NORMAL',
            'color' => 'DORADO',
            'tamano' => 'PEQUENO',
        ]);

        $collarinRojoGrande = Producto::create([
            'codigo' => 'COL-NOR-ROJ-GRA',
            'nombre' => 'Collarín normal rojo grande',
            'tipo_producto' => 'COLLARIN',
            'descripcion' => 'Collarín normal color rojo, tamaño grande.',
            'precio_alquiler' => 20.00,
            'stock_total' => 10,
            'stock_disponible' => 10,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);

        ProductoCollarin::create([
            'producto_id' => $collarinRojoGrande->id,
            'tipo_collarin' => 'NORMAL',
            'color' => 'ROJO',
            'tamano' => 'GRANDE',
        ]);

        $collarinUniversitario = Producto::create([
            'codigo' => 'COL-UNI',
            'nombre' => 'Collarín universitario',
            'tipo_producto' => 'COLLARIN',
            'descripcion' => 'Collarín universitario sin color ni tamaño específico.',
            'precio_alquiler' => 25.00,
            'stock_total' => 12,
            'stock_disponible' => 12,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);

        ProductoCollarin::create([
            'producto_id' => $collarinUniversitario->id,
            'tipo_collarin' => 'UNIVERSITARIO',
            'color' => null,
            'tamano' => null,
        ]);
    }
}