<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['usuario', 'nombres', 'apellidos', 'name', 'email', 'password', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * La columna "name" (de Laravel) se mantiene igual al nombre completo.
     */
    protected static function booted(): void
    {
        static::saving(function (User $usuario) {
            $completo = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? ''));
            $usuario->name = $completo !== '' ? $completo : ($usuario->name ?: (string) $usuario->usuario);
        });
    }

    /**
     * Nombre y apellido, como se imprime en "Representante o encargado del
     * alquiler", en el recibo y en las cartas.
     */
    public function getNombreCompletoAttribute(): string
    {
        $nombre = trim(($this->nombres ?? '') . ' ' . ($this->apellidos ?? ''));

        return $nombre !== '' ? $nombre : (string) ($this->name ?? $this->usuario);
    }

    public function alquileres()
    {
        return $this->hasMany(Alquiler::class, 'usuario_id');
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'usuario_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'usuario_id');
    }

    /**
     * Primer nombre y primer apellido ("Eleazar Flores" para "Eleazar Josué
     * Flores García"). Es lo que se llena en "Representante o encargado del
     * alquiler". Respeta apellidos compuestos como "De León" o "de la Cruz".
     */
    public function getNombreCortoAttribute(): string
    {
        $primerNombre = strtok(trim((string) $this->nombres), ' ') ?: '';
        $primerApellido = self::primerApellido((string) $this->apellidos);
        $corto = trim($primerNombre . ' ' . $primerApellido);

        return $corto !== '' ? $corto : $this->nombre_completo;
    }

    private static function primerApellido(string $apellidos): string
    {
        $particulas = ['de', 'del', 'la', 'las', 'los', 'y', 'san', 'santa', 'van', 'von', 'da', 'di', 'do', 'dos'];
        $palabras = preg_split('/\s+/', trim($apellidos), -1, PREG_SPLIT_NO_EMPTY);
        $resultado = [];

        foreach ($palabras as $palabra) {
            $resultado[] = $palabra;

            if (!in_array(mb_strtolower($palabra), $particulas, true)) {
                break;
            }
        }

        return implode(' ', $resultado);
    }
}
