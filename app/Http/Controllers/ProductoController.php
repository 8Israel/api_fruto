<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\CloudinaryService;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{

    public function show(Request $request, $id = null)
    {
        // Obtener por ID
        if ($id) {
            $producto = Producto::find($id);
            if ($producto) {
                return response()->json(['productos' => [$producto]], 200);
            } else {
                return response()->json(['error' => 'Producto no encontrado'], 404);
            }
        }
    
        // Obtener por nombre
        if ($nombre = $request->input('nombre')) {
            $data = Producto::where('nombre', $nombre)->get();
            if ($data->isNotEmpty()) {
                return response()->json(['productos' => $data], 200);
            } else {
                return response()->json(['error' => 'No se encontró el producto solicitado'], 404);
            }
        }
    
        // Obtener por estado
        if ($request->has('status')) {
            $status = $request->input('status');
            $data = Producto::where('activo', $status)->get();
        } else {
            // Obtener todos los productos si no se proporcionan otros criterios
            $data = Producto::all();
        }
    
        // Respuesta para todos los productos/filtrados
        if ($data->isNotEmpty()) {
            return response()->json(['productos' => $data], 200);
        } else {
            return response()->json(['error' => 'No se encontraron productos'], 404);
        }
    }

    public function create(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'nombre' => 'required|unique:producto|min:3|max:60',
            'categoria' => 'required|numeric|max_digits:4|min_digits:1',
            'img' => 'nullable|image|mimes:png,jpg'
        ]);
        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors()], 400);
        }

        $newproducto = new Producto();
        $newproducto->nombre = $request->input('nombre');
        $newproducto->categoria = $request->input('categoria');

        if ($request->hasFile('img')) {
            $image = $request->file('img')->storeOnCloudinary('api_fruto/productos');
            $path = $image->getSecurePath();

            $newproducto->img = $path;
        }

        $newproducto->save();
        return response()->json(['msj' => 'Producto creado correctamente', 'data' => $newproducto], 200);
    }

    public function update(Request $request, $id)
{
    $validate = Validator::make($request->all(), [
        'nombre' => 'nullable|unique:producto|min:3|max:60',
        'categoria' => 'nullable|numeric|max_digits:4|min_digits:1',
        'img' => 'nullable|image|mimes:png,jpg'
    ]);
    if ($validate->fails()) {
        return response()->json(['error' => $validate->errors()], 400);
    }

    $producto = Producto::find($id);
    if ($producto) {
        if ($request->has('nombre')) {
            $producto->nombre = $request->input('nombre');
        }
        if ($request->has('categoria')) {
            $producto->categoria = $request->input('categoria');
        }
        if ($request->hasFile('img')) {
            $image = $request->file('img')->storeOnCloudinary('api_fruto/productos');
            $path = $image->getSecurePath();
            $producto->img = $path;
        }
        $producto->save();
        return response()->json(['msj' => 'Producto actualizado correctamente', 'data' => $producto], 200);
    } else {
        return response()->json(['error' => 'Producto no encontrado'], 404);
    }
}

    public function delete($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            $producto->activo = !$producto->activo;
            $producto->save();
            return response()->json(['msj' => 'Estado del producto cambiado correctamente', 'data' => $producto], 200);
        } else {
            return response()->json(['error' => 'No se encontró el producto señalado'], 404);
        }
    }
}
