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

        // Obtener el parámetro de orden
        $orden = $request->input('orden', 'desc'); // 'asc' por defecto

        // Validar el parámetro de orden
        if (!in_array($orden, ['asc', 'desc'])) {
            return response()->json(["error" => "Orden no válido, use 'asc' o 'desc'"], 400);
        }

        // Ordenar por peso_Neto según el parámetro de orden
        $query->orderBy('peso_Neto', $orden);

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

        // Obtener el parámetro de orden
        $orden = $request->input('orden', 'desc'); // 'asc' por defecto

        // Validar el parámetro de orden
        if (!in_array($orden, ['asc', 'desc'])) {
            return response()->json(["error" => "Orden no válido, use 'asc' o 'desc'"], 400);
        }

        // Ordenar por peso_Neto según el parámetro de orden
        $query->orderBy('peso_Neto', $orden);

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
            $image = $request->file('img')->storeOnCloudinary('api_fruto/registros_inventario');
            $path = $image->getSecurePath();
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
        $idTipo = $request->input('idTipo'); // Obtener el tipo de inventario desde la solicitud
        $fecha = $request->input('fecha');

        if (!$idTipo) {
            return response()->json(["error" => "Tipo de inventario requerido"], 400);
        }

        // Filtrar por tipo de inventario y fecha si se proporciona
        $query = Inventario::selectRaw('DATE(created_at) as fecha')
            ->where('id_tipo', $idTipo);

        if ($fecha) {
            $query->whereDate('created_at', $fecha);
        }

        $fechas = $query->distinct()
            ->orderBy('fecha', 'desc') // Ordenar de más reciente a más antigua
            ->get();

        return response()->json(["fechas" => $fechas]);
    }

    public function showDatosInventariosAgrupados(Request $request)
    {
        // Obtener la fecha de la solicitud
        $fecha = $request->input('fecha');
        if (!$fecha) {
            return response()->json(["error" => "Fecha requerida"], 400);
        }

        // Obtener los filtros opcionales
        $nombreProducto = $request->input('nombre');
        $idProducto = $request->input('id_producto');
        $orden = $request->input('orden', 'asc'); // 'asc' por defecto, se puede cambiar a 'desc'

        // Validar el parámetro de orden
        if (!in_array($orden, ['asc', 'desc'])) {
            return response()->json(["error" => "Orden no válido, use 'asc' o 'desc'"], 400);
        }

        // IDs de los tipos de inventario para estantes y cuarto frío
        $idEstantes = 1;
        $idCuartoFrio = 2;

        // Consultar inventario de estantería
        $inventarioEstanteriaQuery = Inventario::where('id_tipo', $idEstantes)
            ->whereDate('created_at', $fecha)
            ->with('producto', 'contenedor');

        // Aplicar filtros de búsqueda
        if ($nombreProducto) {
            $inventarioEstanteriaQuery->whereHas('producto', function ($query) use ($nombreProducto) {
                $query->where('nombre', 'LIKE', '%' . $nombreProducto . '%');
            });
        }

        if ($idProducto) {
            $inventarioEstanteriaQuery->where('id_Producto', $idProducto);
        }

        $inventarioEstanteria = $inventarioEstanteriaQuery->get();

        // Consultar inventario de cuarto frío
        $inventarioCuartoFrioQuery = Inventario::where('id_tipo', $idCuartoFrio)
            ->whereDate('created_at', $fecha)
            ->with('producto', 'contenedor');

        // Aplicar filtros de búsqueda
        if ($nombreProducto) {
            $inventarioCuartoFrioQuery->whereHas('producto', function ($query) use ($nombreProducto) {
                $query->where('nombre', 'LIKE', '%' . $nombreProducto . '%');
            });
        }

        if ($idProducto) {
            $inventarioCuartoFrioQuery->where('id_Producto', $idProducto);
        }

        $inventarioCuartoFrio = $inventarioCuartoFrioQuery->get();

        // Agrupar inventarios por id de producto
        $productosAgrupados = [];

        // Procesar inventario de estantería
        foreach ($inventarioEstanteria as $registro) {
            $idProducto = $registro->id_Producto;
            if (!isset($productosAgrupados[$idProducto])) {
                $productosAgrupados[$idProducto] = [
                    'producto' => $registro->producto,
                    'inventario_estanteria' => null,
                    'inventario_cuarto_frio' => null,
                    'peso_neto_total' => 0
                ];
            }

            $productosAgrupados[$idProducto]['inventario_estanteria'] = $registro;
            $productosAgrupados[$idProducto]['peso_neto_total'] += (float) $registro->peso_Neto;
        }

        // Procesar inventario de cuarto frío
        foreach ($inventarioCuartoFrio as $registro) {
            $idProducto = $registro->id_Producto;
            if (!isset($productosAgrupados[$idProducto])) {
                $productosAgrupados[$idProducto] = [
                    'producto' => $registro->producto,
                    'inventario_estanteria' => null,
                    'inventario_cuarto_frio' => null,
                    'peso_neto_total' => 0
                ];
            }

            $productosAgrupados[$idProducto]['inventario_cuarto_frio'] = $registro;
            $productosAgrupados[$idProducto]['peso_neto_total'] += (float) $registro->peso_Neto;
        }

        // Convertir a lista indexada
        $productosAgrupados = array_values($productosAgrupados);

        // Ordenar la lista por peso neto total
        usort($productosAgrupados, function ($a, $b) use ($orden) {
            if ($orden === 'asc') {
                return $a['peso_neto_total'] <=> $b['peso_neto_total'];
            } else {
                return $b['peso_neto_total'] <=> $a['peso_neto_total'];
            }
        });

        // Formatear la respuesta
        $respuesta = [
            'Inventario' => []
        ];

        foreach ($productosAgrupados as $producto) {
            $respuesta['Inventario'][] = [
                'producto' => $producto['producto'],
                'inventario_estanteria' => $producto['inventario_estanteria'],
                'inventario_cuarto_frio' => $producto['inventario_cuarto_frio'],
                'peso_neto_total' => $producto['peso_neto_total']
            ];
        }

        return response()->json($respuesta);
    }

}
