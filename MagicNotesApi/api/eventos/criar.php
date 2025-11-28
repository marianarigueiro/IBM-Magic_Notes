<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(empty($data->usuario_id) || empty($data->titulo) || empty($data->data) || empty($data->hora_inicio) || empty($data->hora_fim)){
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Dados obrigatórios faltando"
    ]);
    exit;
}

try {
    $query = "INSERT INTO eventos (usuario_id, titulo, data, hora_inicio, hora_fim, descricao, cor) 
              VALUES (:usuario_id, :titulo, :data, :hora_inicio, :hora_fim, :descricao, :cor)";
    
    $stmt = $db->prepare($query);
    
    $usuario_id = $data->usuario_id;
    $titulo = trim($data->titulo);
    $dataEvento = $data->data;
    $hora_inicio = $data->hora_inicio;
    $hora_fim = $data->hora_fim;
    $descricao = isset($data->descricao) ? trim($data->descricao) : '';
    $cor = isset($data->cor) ? $data->cor : '#93221F';
    
    $stmt->bindParam(":usuario_id", $usuario_id);
    $stmt->bindParam(":titulo", $titulo);
    $stmt->bindParam(":data", $dataEvento);
    $stmt->bindParam(":hora_inicio", $hora_inicio);
    $stmt->bindParam(":hora_fim", $hora_fim);
    $stmt->bindParam(":descricao", $descricao);
    $stmt->bindParam(":cor", $cor);
    
    if($stmt->execute()){
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Evento criado com sucesso",
            "id" => $db->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Erro ao criar evento"
        ]);
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
    ]);
}
?>