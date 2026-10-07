<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuracion de Simulacro - ATHENA</title>
    <style>
        body { font-family: sans-serif; margin: 24px; color: #111; }
        h1 { margin-bottom: 4px; }
        h2 { margin: 24px 0 8px; font-size: 16px; color: #444; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; font-size: 14px; word-break: break-word; }
        th { background: #f2f2f2; }
        tr:nth-child(even) { background: #fafafa; }
        p.empty { color: #888; }
    </style>
</head>
<body>
    <h1>Configuracion de Simulacro</h1>

@php
    $vars = get_defined_vars();
    unset($vars['__env'], $vars['view'], $vars['vars']);
@endphp

@if (empty($vars))
    <p class="empty">Sin datos</p>
@endif

@foreach ($vars as $nombre => $valor)
    <h2>{{ $nombre }}</h2>
    @php
        $filas = [];

        if ($valor instanceof \Illuminate\Support\Collection) {
            $valor = $valor->toArray();
        }

        if (is_array($valor)) {
            if (array_is_list($valor)) {
                foreach ($valor as $item) {
                    if (is_array($item)) {
                        $filas[] = $item;
                    } elseif (is_object($item) && method_exists($item, 'toArray')) {
                        $filas[] = $item->toArray();
                    } elseif (is_object($item)) {
                        $filas[] = get_object_vars($item);
                    } else {
                        $filas[] = ['valor' => $item];
                    }
                }
            } else {
                $filas[] = $valor;
            }
        } elseif (is_object($valor) && method_exists($valor, 'toArray')) {
            $filas[] = $valor->toArray();
        } elseif (is_object($valor)) {
            $filas[] = get_object_vars($valor);
        } elseif ($valor === null || $valor === '') {
            $filas = [];
        } else {
            $filas[] = ['valor' => $valor];
        }

        $columnas = [];
        foreach ($filas as $fila) {
            foreach (array_keys($fila) as $col) {
                $columnas[$col] = true;
            }
        }
        $columnas = array_keys($columnas);
    @endphp

    @if (!count($filas))
        <p class="empty">Sin datos</p>
    @else
        <table>
            <thead>
                <tr>@foreach ($columnas as $col)<th>{{ $col }}</th>@endforeach</tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    <tr>
                        @foreach ($columnas as $col)
                            <td>@php
                                $celda = $fila[$col] ?? '';
                                if (is_array($celda) || is_object($celda)) {
                                    $celda = json_encode($celda, JSON_UNESCAPED_UNICODE);
                                }
                                echo e($celda);
                            @endphp</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endforeach
</body>
</html>
