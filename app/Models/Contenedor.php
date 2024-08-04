<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contenedor extends Model
{
    use HasFactory;

    protected $table = 'peso_contenedor';

    protected $fillable = [
        'nombre',
        'peso',
        'img',
        'activo'
    ]; 
    public $timestamps = false;

    public function inventario(){
        return $this->hasOne(Inventario::class);
    }
}
