<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'producto';

    public $timestamps = false;
    public $fillable=[
        'nombre',
        'categoria',
        'img',
        'activo'
    ];

    public function inventario(){
        return $this -> hasMany(Inventario::class, 'id_producto');
    }
    public function categoria(){
        return $this -> belongsTo(Categoria::class);
    }
}
