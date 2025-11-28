<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(empty($data->id)){
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "ID do evento obrigatório"
    ]);
    exit;
}

try {
    $query = "DELETE FROM eventos WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $data->id);
    
    if($stmt->execute()){
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Evento deletado com sucesso"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Erro ao deletar evento"
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