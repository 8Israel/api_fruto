<?php

namespace App\Http\Controllers;

use App\Models\Contenedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContenedorController extends Controller
{
    public function show(Request $request, $id = null)
    {
        if($id){
            $data = Contenedor::find($id);
            return response()->json(['data'=> [$data]],200);
        }

        if($request->has('status'))
        {
            $data = Contenedor::where('activo', $request->input('status'))->get();
            if ($data->isNotEmpty()){
                return response()->json(['data'=> $data],200);
            }
            else{
                return response()->json(['errors'=>'No se encontraron contenedores'],404);
            }
        }
        else{
            $data = Contenedor::all();
            return response()->json(['data'=> $data]);
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
            $image = $request->file('img')->storeOnCloudinary('api_fruto/contenedores');
            $path = $image->getSecurePath();
            $contenedor->img = $path;
        }
        $contenedor->save();
        return response()->json(['msj' => 'contenedor creado correctamente', 'data'=>$contenedor], 200);
    }
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'nombre' => 'unique:peso_contenedor|nullable',
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
                $image = $request->file('img')->storeOnCloudinary('api_fruto/contenedores');
                $path = $image->getSecurePath();
                $contenedor->img = $path;
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

            if($bol == 1){
                $contenedor->activo = 0;
            }
            else if($bol == 0){
                $contenedor->activo = 1;
            }
            $contenedor->save();
            return response()->json(['msj' => 'Estado del contenedor cambiado correctamente', 'data'=>$contenedor], 200);

        }
        else{
            return response()->json(['errors'=>'contenedor no encontrado'],404);
        }
        
    }

}

#git commit -m "Create ContenderoController arreglado"