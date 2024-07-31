<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_Tipo',
        'id_Producto',
        'id_Contenedor',
        'peso_kg',
        'num_Contenedor',
        'peso_Neto',
        'comentarios',
        'img'
    ];
    protected $table = 'inventario';

    
    public function producto(){
        return $this-> belongsTo(Producto::class, 'id_Producto');
    }

    public function contenedor(){
        return $this-> belongsTo(Contenedor::class, 'id_Contenedor');
    }

    public function tipo_inventario(){
        return $this-> belongsTo(Inventario_tipo::class, 'id_Tipo');
    }
}
