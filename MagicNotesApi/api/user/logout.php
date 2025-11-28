<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Pegar token do header
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

if(empty($token)){
    http_response_code(401);
    echo json_encode(array(
        "success" => false,
        "message" => "Token não fornecido"
    ));
    exit;
}

// Em uma implementação real com JWT ou sessões, você invalidaria o token aqui
// Como estamos usando tokens simples em base64, apenas confirmamos o logout

try {
    // Validar se o token é válido antes de fazer logout
    $token_parts = explode('|', base64_decode($token));
    
    if(count($token_parts) >= 3){
        // Token válido, fazer logout
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Logout realizado com sucesso"
        ));
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Token inválido"
        ));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro ao processar logout"
    ));
}
?>