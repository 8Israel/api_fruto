<?php

namespace App\Http\Controllers;

use App\Models\Inventario_tipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventarioTipoController extends Controller
{
    public function show(){
        $data = Inventario_tipo::where('activo', 1)->get();
        if($data->isNotEmpty()){
            return response()->json(['tipos_inventarios'=>$data], 200);
        }
        else{
            return response()->json(['errors'=>'Tipos de inventarios no encontrados']);
        }
    }
    public function create(Request $request){
        $validate = Validator::make($request->all(),[
            'nombre'=> 'required|unique:tipo_inventario|min:5|max:60'
        ]);
        if($validate->fails()){
            return response()->json(['errors'=>$validate->errors()], 400);
        }
        $newInventario = Inventario_tipo::create($request->all());
        return response()->json(['msj'=>'Tipo de inventario creado satisfactoriamente', 'tipo_inventario'=>$newInventario],200);
    }
    public function update(Request $request, $id){
        $validate = Validator::make($request->all(),[
            'nombre'=> 'unique:tipo_inventario|min:5|max:60'
        ]);
        if($validate->fails()){
            return response()->json(['errors'=>$validate->errors()], 400);
        }
        $inventario = Inventario_tipo::find($id);
        if($inventario){
            $inventario->nombre = $request->input('nombre');
            $inventario->save();
            return response()->json(['msj'=>'Actualizacion de registros completada satisfactoriamente'],200);
        }else{
            return response()->json(['error'=>'No se encontro el tipo de inventario señalado'],404);
        }
        
    }
    public function delete($id){
        $inventario = Inventario_tipo::find($id);
        if($inventario){
            $bol = $inventario->activo;
            if($bol == true){
                $inventario->activo = false;
            }
            elseif($bol == false){
                $inventario->activo = true;
            }
            $inventario->save();
            return response()->json(['msj'=>'Estado del tipo de iventario cambiada correctamente', 'data'=>$inventario],200);
        }else{
            return response()->json(['error'=>'No se encontro el tipo de inventario señalado'],404);
        }
    }
}
