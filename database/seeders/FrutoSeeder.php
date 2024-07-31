<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrutoSeeder extends Seeder
{
    public function run()
    {
        #Categorias
        DB::table('categoria')->insert([
            'nombre'=> 'frutas'
        ]);
        DB::table('categoria')->insert([
            'nombre'=> 'verduras'
        ]);
        DB::table('categoria')->insert([
            'nombre'=> 'abarrotes'
        ]);

        #Contenedores
        DB::table('peso_contenedor')->insert([
            'nombre'=> 'null',
            'peso'=> '0.00',
            'img'=> 'NO HAY',
        ]);
        DB::table('peso_contenedor')->insert([
            'nombre'=> 'platanera',
            'peso'=> '2.000',
            'img'=> 'NO HAY',
        ]);
        DB::table('peso_contenedor')->insert([
            'nombre'=> 'aguacatera',
            'peso'=> '0.500',
            'img'=> 'NO HAY',
        ]);
        DB::table('peso_contenedor')->insert([
            'nombre'=> 'guayavera',
            'peso'=> '0.800',
            'img'=> 'NO HAY',
        ]);
        DB::table('peso_contenedor')->insert([
            'nombre'=> 'tomatera',
            'peso'=> '1.800',
            'img'=> 'NO HAY',
        ]);

        #Tipo de inventarios
        DB::table('tipo_inventario')->insert([
            'nombre'=> 'Entrada'
        ]);
        DB::table('tipo_inventario')->insert([
            'nombre'=> 'Cuarto frio'
        ]);
        DB::table('tipo_inventario')->insert([
            'nombre'=> 'Merma'
        ]);

        #Productos
        //Frutas
        DB::table('producto')->insert([
            'nombre'=> 'Manzana',
            'categoria'=> '1',
            'img'=> 'No hay',
        ]);
        DB::table('producto')->insert([
            'nombre'=> 'platano',
            'categoria'=> '1',
            'img'=> 'No hay',
        ]);
        DB::table('producto')->insert([
            'nombre'=> 'naranja',
            'categoria'=> '1',
            'img'=> 'No hay',
        ]);
        DB::table('producto')->insert([
            'nombre'=> 'durazno',
            'categoria'=> '1',
            'img'=> 'No hay',
        ]);
        DB::table('producto')->insert([
            'nombre'=> 'guayaba',
            'categoria'=> '1',
            'img'=> 'No hay',
        ]);

        //Verduras
        DB::table('producto')->insert([
            'nombre'=> 'Papa',
            'categoria'=> '2',
            'img'=> 'No hay',
        ]);
        DB::table('producto')->insert([
            'nombre'=> 'cebolla',
            'categoria'=> '2',
            'img'=> 'No hay',
        ]);
        DB::table('producto')->insert([
            'nombre'=> 'tomate',
            'categoria'=> '2',
            'img'=> 'No hay',
        ]);
        DB::table('producto')->insert([
            'nombre'=> 'chile jalapeño v',
            'categoria'=> '2',
            'img'=> 'No hay',
        ]);
        

    }
}
