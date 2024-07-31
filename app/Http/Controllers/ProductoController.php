<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function show(Request $request, $id = null){
        if($id){
            $data = Producto::where('id', $id)->get();
            return response()->json(['productos'=>$data], 200);
        }
        if($request->input('nombre')){
            $data = Producto::where('nombre', $request->input('nombre'))->where('activo', 1)->get();
            if($data->isNotEmpty()){
                return response()->json(['productos'=>$data], 200);
            }
            else{
                return response()->json(['error'=>'No se encontro el producto solicitado'],404);
            }    
        }
        $data = Producto::where('activo', 1)->get();
        if($data->isNotEmpty()){
            return response()->json(['productos'=>$data], 200);
        }
        else{
            return response()->json(['error'=>'No se encontraron productos activos'],404);
        }
    }
    public function create(Request $request){
        $validate = Validator::make($request->all(),[
            'nombre' => 'required|unique:producto|min:3|max:60',
            'categoria' => 'required|numeric|max_digits:4|min_digits:1',
            'img' => 'nullable|image:png, jpg'
        ]);
        if($validate->fails()){
            return response()->json(['error'=>$validate->errors()],400);
        }
        $newproducto = Producto::create();
        $newproducto->nombre = $request->input('nombre');
        $newproducto->categoria = $request->input('categoria');
        if($request->hasFile('img')){
            $path = $request->file('img')->store('/public/productos');
            $newproducto->img = $path;
        }
        $newproducto->save();
        return response()->json(['msj'=>'producto creado correctamente', 'data'=>$newproducto],200);
    }
    public function update(Request $request, $id){
        $validate = Validator::make($request->all(),[
            'nombre' => 'nullable|unique:producto|min:3|max:60',
            'categoria' => 'nullable|numeric|max_digits:4|min_digits:1',
            'img' => 'nullable|image:png, jpg'
        ]);
        if($validate->fails()){
            return response()->json(['error'=>$validate->errors()],400);
        }
        
        $producto = Producto::find($id);
        if ($producto){
            if ($request->has('nombre')) {
                $producto->nombre = $request->input('nombre');
            }
            if ($request->has('categoria')) {
                $producto->categoria = $request->input('categoria');
            }
            if ($request->has('img')) {
                $newpath = $request->file('img')->store('public/productos');
                $producto->img = $newpath;
            }
            $producto->save();
            return response()->json(['msj' => 'producto actualizado correctamente', 'data'=>$producto], 200);
        }
        else{
            return response()->json(['errors'=>'producto no encontrado'],404);
        }
    }
    public function delete($id){
        $producto = Producto::find($id);
        if($producto){
            $bol = $producto->activo;
            if($bol == true){
                $producto->activo = false;
            }
            elseif($bol == false){
                $producto->activo = true;
            }
            $producto->save();
            return response()->json(['msj'=>'Estado del producto cambiada correctamente', 'data'=>$producto],200);
        }else{
            return response()->json(['error'=>'No se encontro el producto señalado'],404);
        }
    }
}
