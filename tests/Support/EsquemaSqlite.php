<?php

namespace Tests\Support;

/**
 * Prepara las migraciones del proyecto para poder correrlas en SQLite (la base
 * de las pruebas). La base real es MySQL y NO se toca: esto solo genera una
 * copia temporal de las migraciones con dos adaptaciones:
 *
 *  1. Las columnas ENUM pasan a texto (SQLite las convierte en CHECK y el
 *     proyecto ya amplió esos ENUM varias veces con comandos solo de MySQL).
 *  2. Se omiten las migraciones cuyo único trabajo es `ALTER TABLE ... MODIFY`
 *     (sintaxis que SQLite no entiende).
 *  3. Se omiten las migraciones de la lista OMITIR (ver más abajo).
 */
final class EsquemaSqlite
{
    private static ?string $ruta = null;

    /**
     * Migraciones que no se pueden correr sobre una base vacía.
     *
     * 2026_07_29_100328 intenta borrar `producto_birretes.carrera`, una columna
     * que ninguna migración anterior crea (existía en la base real por otro
     * camino). En una base nueva falla también en MySQL; la migración
     * 2026_07_29_100611 ya hace ese mismo borrado de forma segura.
     */
    private const OMITIR = [
        '2026_07_29_100328_remove_carrera_from_producto_birretes_table.php',
    ];

    public static function rutaMigraciones(): string
    {
        if (self::$ruta !== null && is_dir(self::$ruta)) {
            return self::$ruta;
        }

        $destino = sys_get_temp_dir() . '/cpc-togas-migraciones-' . getmypid();

        if (!is_dir($destino)) {
            mkdir($destino, 0777, true);
        }

        foreach (glob(database_path('migrations/*.php')) as $archivo) {
            if (in_array(basename($archivo), self::OMITIR, true)) {
                continue;
            }

            $codigo = file_get_contents($archivo);

            $soloModificaConMysql = preg_match('/MODIFY/i', $codigo)
                && !str_contains($codigo, "config('database.default')");

            if ($soloModificaConMysql) {
                continue;
            }

            $codigo = preg_replace(
                "/->enum\(\s*'([^']+)'\s*,\s*\[[^\]]*\]\s*\)/s",
                "->string('$1')",
                $codigo
            );

            file_put_contents($destino . '/' . basename($archivo), $codigo);
        }

        return self::$ruta = $destino;
    }
}
