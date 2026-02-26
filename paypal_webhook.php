<?php
// Configuración de UltraMsg
$ultramsg_token = "72dtaq4u270hmtw5"; 
$instance_id = "instance163295";
$mi_whatsapp = "+50588385491"; // Tu número con código de país

// 1. Recibir la notificación automática de PayPal
$json = file_get_contents('php://input');
$datos = json_decode($json, true);

// 2. Verificar que el evento sea un pago exitoso
if ($datos['event_type'] == 'CHECKOUT.ORDER.APPROVED' || $datos['event_type'] == 'PAYMENT.CAPTURE.COMPLETED') {

    // 3. Extraer información del producto y cliente
    // Dependiendo de tu configuración de PayPal, los campos pueden variar ligeramente
    $cliente = $datos['resource']['payer']['name']['given_name'] ?? "Cliente";
    $monto = $datos['resource']['purchase_units'][0]['amount']['value'] ?? "0.00";
    $moneda = $datos['resource']['purchase_units'][0]['amount']['currency_code'] ?? "USD";
    $producto = $datos['resource']['purchase_units'][0]['description'] ?? "Producto sin descripción";
    $orden_id = $datos['resource']['id'];

    // 4. Armar el mensaje personalizado
    $mensaje = "✅ *¡NUEVO PAGO CONFIRMADO!* \n\n";
    $mensaje .= "📦 *Producto:* " . $producto . "\n";
    $mensaje .= "💰 *Monto:* " . $monto . " " . $moneda . "\n";
    $mensaje .= "👤 *Cliente:* " . $cliente . "\n";
    $mensaje .= "🆔 *ID Orden:* " . $orden_id;

    // 5. Enviar a UltraMsg mediante CURL
    $params = array(
        'token' => $ultramsg_token,
        'to' => $mi_whatsapp,
        'body' => $mensaje
    );

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => http_build_query($params),
      CURLOPT_HTTPHEADER => array("content-type: application/x-www-form-urlencoded"),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
}

// Responder a PayPal con un código 200 para que sepa que recibimos el mensaje
http_response_code(200);
?>