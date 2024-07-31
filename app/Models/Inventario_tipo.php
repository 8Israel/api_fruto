<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario_tipo extends Model
{
    use HasFactory;
    protected $table = 'tipo_inventario';
    public $timestamps = false;
    public $fillable = [
        'nombre'
    ];
    public $hidden = [
        'activo'
    ];

    public function inventario()
    {
        return $this->hasOne(Inventario::class);
    }
}