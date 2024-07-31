<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    public function show()
    {
        $categorias = Categoria::where('activo', 1)->get();

        if ($categorias->isNotEmpty()) {
            return response()->json(['data'=>$categorias],200);
        } else {
            return response()->json(['error'=>'no hay categorias disponibles'],404);
        }
    }
    public function create(Request $request)
    {
        if ($request) {
            $validation = Validator::make($request->all(), [
                'nombre' => 'required|max:25|unique:categoria'
            ]);
            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 400);
            }
            $categoria = Categoria::create([
                'nombre' => $request->nombre
            ]);
            return response()->json(['categoria' => $categoria]);
        } else {
            return false;
        }
    }
    public function update(Request $request, $id)
    {
        $categoria = Categoria::find($id);
        if ($categoria) {

            $validation = Validator::make($request->all(), [
                'nombre' => 'required|max:25|unique:categoria'
            ]);
            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 400);
            }

            $categoria->nombre = $request->input('nombre');
            $categoria->save();

            return response()->json([$categoria]);
        } else {
            return response()->json(['error' => 'categoria no encontrada'], 404);
        }
    }
    public function delete($id)
    {
        $categoria = Categoria::find($id);
        if ($categoria && $categoria->activo == 1) {
            $categoria->activo = false;
            $categoria->save();

            return response()->json(['msj' => 'categoria eliminada correctamente'],200);
        } else {
            return response()->json(['error' => 'categoria no encontrada']);
        }
    }
}
