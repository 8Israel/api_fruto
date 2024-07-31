<?php

namespace App\Http\Controllers;

use App\Models\Contenedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContenedorController extends Controller
{
    public function show()
    {
        $data = Contenedor::where('activo', 1)->get();
        if ($data->isNotEmpty()){
            return response()->json(['data'=> $data],200);
        }
        else{
            return response()->json(['errors'=>'No se encontraron contenedores'],404);
        }
    }
    public function create(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'nombre' => 'required|unique:peso_contenedor',
            'peso' => 'required|numeric',
            'img' => 'nullable|image:png, jpg'
        ]);
        if ($validate->fails()) {
            return response()->json(['errors' => $validate->errors()], 400);
        }
        $contenedor = Contenedor::create();
        $contenedor->nombre = $request->input('nombre');
        $contenedor->peso = $request->input('peso');

        if ($request->hasFile('img')) {
            $path = $request->file('img')->store('/public/contenedores');
            $contenedor->img = $path;
        }
        $contenedor->save();
        return response()->json(['msj' => 'contenedor creado correctamente', 'data'=>$contenedor], 200);
    }
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'nombre' => 'unique:peso_contenedor',
            'peso' => 'nullable|numeric',
            'img' => 'nullable|image:png, jpg'
        ]);
        if ($validate->fails()) {
            return response()->json([
                $validate->errors()
            ], 400);
        }
        $contenedor = Contenedor::find($id);
        if ($contenedor){
            if ($request->has('nombre')) {
                $contenedor->nombre = $request->input('nombre');
            }
            if ($request->has('peso')) {
                $contenedor->peso = $request->input('peso');
            }
            if ($request->has('img')) {
                $newpath = $request->file('img')->store('public/contenedores');
                $contenedor->img = $newpath;
            }
            $contenedor->save();
            return response()->json(['msj' => 'contenedor actualizado correctamente', 'data'=>$contenedor], 200);
        }
        else{
            return response()->json(['errors'=>'contenedor no encontrado'],404);
        }
    }

    public function delete($id)
    {
        $contenedor = Contenedor::find($id);
        if($contenedor){
            $bol = $contenedor->activo;

            if($bol == true){
                $contenedor->activo = false;
            }
            else if($bol == false){
                $contenedor->activo = true;
            }
            $contenedor->save();
            return response()->json(['msj' => 'Estado del contenedor cambiado correctamente', 'data'=>$contenedor], 200);

        }
        else{
            return response()->json(['errors'=>'contenedor no encontrado'],404);
        }
        
    }

}
