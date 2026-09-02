<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Método no permitido.']);
    exit;
}

/* Este correo es privado y no se muestra en la landing. */
$destinatario = 'oscar.ronaldo.96@gmail.com';

$nombre = trim($_POST['nombre'] ?? '');
$negocio = trim($_POST['negocio'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$tipo = trim($_POST['tipo_negocio'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if ($nombre === '' || $negocio === '' || $telefono === '' || $email === '' || $tipo === '' || $mensaje === '') {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Por favor completa todos los campos.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'El correo electrónico no es válido.']);
    exit;
}

$email = str_replace(["\r","\n"], '', $email);
$nombre = str_replace(["\r","\n"], ' ', $nombre);
$negocio = str_replace(["\r","\n"], ' ', $negocio);
$telefono = str_replace(["\r","\n"], ' ', $telefono);
$tipo = str_replace(["\r","\n"], ' ', $tipo);

$asunto = 'Nueva solicitud de Stocky - ' . $negocio;
$cuerpo  = "Nueva persona interesada en Stocky\n\n";
$cuerpo .= "Nombre: $nombre\n";
$cuerpo .= "Negocio: $negocio\n";
$cuerpo .= "WhatsApp / Teléfono: $telefono\n";
$cuerpo .= "Correo: $email\n";
$cuerpo .= "Tipo de negocio: $tipo\n\n";
$cuerpo .= "Mensaje:\n$mensaje\n\n";
$cuerpo .= "Solicitud recibida desde stocky.site.";

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: Stocky <no-reply@stocky.site>',
    'Reply-To: ' . $email
];

if (!mail($destinatario, $asunto, $cuerpo, implode("\r\n", $headers))) {
    http_response_code(500);
    echo json_encode([
        'success'=>false,
        'message'=>'No fue posible enviar la solicitud. Puedes contactarme directamente por WhatsApp.'
    ]);
    exit;
}

echo json_encode([
    'success'=>true,
    'message'=>'¡Perfecto! Recibí tus datos. Me pondré en contacto contigo para explicarte el proceso de activación.'
]);
