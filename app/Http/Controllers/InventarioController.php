<?php

namespace App\Http\Controllers;

use App\Models\Contenedor;
use App\Models\Inventario;
use App\Models\Inventario_tipo;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventarioController extends Controller
{
    public function show(Request $request, $idTipo)
    {
        // Asegúrate de que se proporcione la fecha
        $fecha = $request->input('fecha');
        if (!$fecha) {
            return response()->json(["error" => "Fecha requerida"], 400);
        }

        // Inicia la consulta base
        $query = Inventario::where('id_tipo', $idTipo)
            ->whereDate('created_at', $fecha)
            ->with('producto', 'contenedor');

        // Aplica el filtro opcional por nombre del producto si se proporciona
        if ($request->input('nomProducto')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->input('nomProducto') . '%');
            });
        }

        // Ejecuta la consulta y obtiene los resultados
        $inventario = $query->get();

        return response()->json(["Inventario" => $inventario]);
    }
    public function getRegistroById($id)
    {
        $inventario = Inventario::with('producto', 'contenedor')->find($id);
        if (!$inventario) {
            return response()->json(["error" => "Registro no encontrado"], 404);
        }
        return response()->json($inventario, 200);
    }
    public function showInventariohoy(Request $request, $id_tipo)
    {
        // Inicia la consulta base
        $query = Inventario::where('id_tipo', $id_tipo)
            ->whereDate('created_at', today())
            ->with('producto', 'contenedor');

        // Aplica el filtro opcional por nombre del producto si se proporciona
        if ($request->input('nomProducto')) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->input('nomProducto') . '%');
            });
        }

        // Ejecuta la consulta y obtiene los resultados
        $inventario = $query->get();

        return response()->json(["Inventario" => $inventario]);
    }

    public function check($id)
    {
        $terminados = Inventario::where('id_tipo', $id)->whereDate('created_at', today())->get();

        foreach ($terminados as $terminado) {
            if ($terminado->finalizado == 1) {
                response()->json(["status" => 1]);
            }
        }
        return response()->json(["status" => 0]);
    }
    public function create($tipo_id)
    {
        $productosRestantes = Producto::whereDoesntHave('inventario', function ($query) use ($tipo_id) {
            $query->where('id_tipo', $tipo_id)->whereDate('created_at', today());
        })->where('activo', 1)->get();

        if ($productosRestantes->isNotEmpty()) {
            foreach ($productosRestantes as $producto) {
                $newInventario = new Inventario();
                $newInventario->id_Tipo = $tipo_id;
                $newInventario->id_Producto = $producto->id;
                $newInventario->id_Contenedor = 1;
                $newInventario->peso_kg = 0;
                $newInventario->num_Contenedor = 0;
                $newInventario->peso_Neto = 0;
                $newInventario->comentarios = "No hay";
                $newInventario->img = "null";
                $newInventario->save();
            }
        } else {
            return response()->json(["msj" => "El inventario ya a sido creado"]);
        }

        return response()->json(["message" => "Inventarios creados correctamente", "productosRestantes" => $productosRestantes], 201);
    }
    public function update(Request $request, $id)
    {

        $inventario = Inventario::find($id);
        if (!$inventario) {
            return response()->json(["error" => "Registro no encontrado"]);
        }
        $validate = Validator::make($request->all(), [
            'tipoInventario' => 'nullable|numeric',
            'producto' => 'nullable|numeric',
            'contenedor' => 'nullable|numeric',
            'peso' => 'nullable|numeric',
            'numContenedor' => 'nullable|numeric',
            'comentarios' => 'nullable|min:3|max:60',
            'img' => 'nullable|image:png, jpg',
        ]);
        if ($validate->fails()) {
            return response()->json(["error" => $validate->errors()]);
        }

        if ($request->has('tipoInventario')) {
            $tipo = Inventario_tipo::where([['id', '=', $request->tipoInventario], ['activo', '=', 1]])->get();
            if ($tipo->isEmpty()) {
                return response()->json(['tipo de inventario inexistente'], 404);
            }
            $inventarioexists = Inventario::where([['id_tipo', $request->tipoInventario], ['id_producto', $inventario->id_Producto]])->whereDate('created_at', today())->get();
            if ($inventarioexists->isNotEmpty()) {
                return response()->json(["error" => "El producto ya fue registrado en este tipo de inventario", "data" => $inventarioexists], 406);
            }

            $inventario->id_Tipo = $request->input('tipoInventario');
        }
        if ($request->has('producto')) {
            $producto = Producto::where([['id', $request->producto], ['activo', 1]])->get();
            if ($producto->isEmpty()) {
                return response()->json(['producto inexistente'], 404);
            }
            $inventarioexists = Inventario::where([['id_tipo', $inventario->id_Tipo], ['id_producto', $request->producto]])->whereDate('created_at', today())->get();
            if ($inventarioexists->isNotEmpty()) {
                return response()->json(["error" => "El producto ya fue registrado en este tipo de inventario", "data" => $inventarioexists], 406);
            }
            $inventario->id_Producto = $request->input('producto');
        }
        if ($request->has('contenedor')) {
            $contenedor = Contenedor::where([['id', $request->contenedor], ['activo', 1]])->get();
            if ($contenedor->isEmpty()) {
                return response()->json(['contenendor inexistente'], 404);
            }
            $inventario->id_Contenedor = $request->input('contenedor');
            $newpeso = $this->pesoNeto($inventario->peso_kg, $contenedor, $inventario->num_Contenedor);
            $inventario->peso_Neto = $newpeso;
        }
        if ($request->has('peso')) {
            $contenedor = Contenedor::where([['id', $inventario->id_Contenedor]])->get();
            $newpeso = $this->pesoNeto($request->input('peso'), $contenedor, $inventario->num_Contenedor);
            $inventario->peso_kg = $request->input('peso');
            $inventario->peso_Neto = $newpeso;
        }
        if ($request->has('numContenedor')) {
            $contenedor = Contenedor::where([['id', $inventario->id_Contenedor]])->get();
            $newpeso = $this->pesoNeto($inventario->peso_kg, $contenedor, $request->input('numContenedor'));
            $inventario->peso_Neto = $newpeso;
            $inventario->num_Contenedor = $request->numContenedor;
        }
        if ($request->has('comentarios')) {
            $inventario->comentarios = $request->input('comentarios');
        }
        if ($request->hasFile('img')) {
            $path = $request->file('img')->store('public/inventario');
            $inventario->img = $path;
        }
        $inventario->save();

        return response()->json(["registro actualizado correctamente" => $inventario], 200);
    }

    public function delete($id)
    {
        $inventario = Inventario::find($id);
        if (!$inventario) {
            return response()->json(["error" => "registro no encontrado"], 404);
        }
        $inventario->delete();
        return response()->json(["msj" => "registro eliminado correcta y permanentemente", "data" => $inventario], 200);
    }

    public function terminarInventario($tipo_id)
    {
        $inventario = Inventario::where('id_tipo', $tipo_id)->whereDate('created_at', today())->get();

        foreach ($inventario as $invent) {
            $invent->finalizado = true;
            $invent->save();
        }
        return response()->json([$inventario]);
    }

    public function pesoNeto($peso, $contenedor, $numContenedor)
    {
        $Contenedorpeso = $contenedor->value('peso');
        $pesoTotalContenedor = $Contenedorpeso * $numContenedor;
        $pesoNeto = $peso - $pesoTotalContenedor;

        return $pesoNeto;
    }

    public function getFechasInventarios(Request $request)
    {
        $fecha = $request->input('fecha');

        if ($fecha) {
            $fechas = Inventario::selectRaw('DATE(created_at) as fecha')
                ->whereDate('created_at', $fecha)
                ->distinct()
                ->orderBy('fecha', 'desc') // Ordenar de más reciente a más antigua
                ->get();
        } else {
            $fechas = Inventario::selectRaw('DATE(created_at) as fecha')
                ->distinct()
                ->orderBy('fecha', 'desc') // Ordenar de más reciente a más antigua
                ->get();
        }
        return response()->json(["fechas" => $fechas]);
    }

}
