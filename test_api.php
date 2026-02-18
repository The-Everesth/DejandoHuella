<?php

function requestJson($method, $url, $payload = null)
{
    $headers = ['Accept: application/json'];

    if ($payload !== null) {
        $headers[] = 'Content-Type: application/json';
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'error' => $error,
        'raw' => $response,
        'json' => json_decode($response, true),
    ];
}

function printStep($title)
{
    echo "\n==============================\n";
    echo $title . "\n";
    echo "==============================\n";
}

function printResult($result)
{
    echo "HTTP: {$result['status']}\n";

    if (!empty($result['error'])) {
        echo "cURL Error: {$result['error']}\n";
        return;
    }

    if ($result['json'] === null) {
        echo "Response (raw): {$result['raw']}\n";
        return;
    }

    echo json_encode($result['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

$baseUrl = getenv('BASE_API_URL') ?: 'http://localhost:8000/api/adoptions';
$createdId = null;

echo "Probando API de adopciones en: {$baseUrl}\n";

// 1) CREATE
printStep('1) POST /api/adoptions');
$createPayload = [
    'nombreAnimal' => 'Luna',
    'tipoAnimal' => 'Perro',
    'edad' => 2,
    'raza' => 'Mestizo',
    'detalles' => 'Prueba automática desde test_api.php',
];
$create = requestJson('POST', $baseUrl, $createPayload);
printResult($create);

if (!empty($create['json']['data']['id'])) {
    $createdId = $create['json']['data']['id'];
    echo "ID creado: {$createdId}\n";
} else {
    echo "No se pudo obtener un ID creado. Se detiene la prueba.\n";
    exit(1);
}

// 2) LIST
printStep('2) GET /api/adoptions');
$list = requestJson('GET', $baseUrl);
printResult($list);

// 3) GET ONE
printStep('3) GET /api/adoptions/{id}');
$getOne = requestJson('GET', $baseUrl . '/' . urlencode($createdId));
printResult($getOne);

// 4) UPDATE
printStep('4) PUT /api/adoptions/{id}');
$updatePayload = [
    'estado' => 'aprobada',
    'detalles' => 'Registro actualizado por script de prueba',
];
$update = requestJson('PUT', $baseUrl . '/' . urlencode($createdId), $updatePayload);
printResult($update);

// 5) DELETE
printStep('5) DELETE /api/adoptions/{id}');
$delete = requestJson('DELETE', $baseUrl . '/' . urlencode($createdId));
printResult($delete);

// 6) VERIFY DELETE
printStep('6) Verificar eliminación (GET debe devolver 404)');
$verifyDelete = requestJson('GET', $baseUrl . '/' . urlencode($createdId));
printResult($verifyDelete);

echo "\nFlujo de prueba finalizado.\n";
