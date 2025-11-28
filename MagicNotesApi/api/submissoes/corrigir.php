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

if(empty($data->resposta_id) || !isset($data->nota)){
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "resposta_id e nota são obrigatórios"
    ]);
    exit;
}

try {
    $query = "UPDATE atividades_respostas 
              SET nota = :nota, 
                  feedback = :feedback,
                  status = 'corrigida'
              WHERE id = :resposta_id";
    
    $stmt = $db->prepare($query);
    
    $resposta_id = $data->resposta_id;
    $nota = $data->nota;
    $feedback = isset($data->feedback) ? trim($data->feedback) : null;
    
    $stmt->bindParam(":resposta_id", $resposta_id);
    $stmt->bindParam(":nota", $nota);
    $stmt->bindParam(":feedback", $feedback);
    
    if($stmt->execute()){
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Atividade corrigida com sucesso"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Erro ao corrigir atividade"
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