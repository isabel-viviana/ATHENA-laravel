<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulacro Rápido - ATHENA</title>
</head>
<body>
    <h1>Simulacro Rápido</h1>
@php
    $payload = get_defined_vars();
    unset($payload['__env'], $payload['view'], $payload['payload']);
@endphp
    <pre>@json($payload, JSON_PRETTY_PRINT)</pre>
</body>
</html>
