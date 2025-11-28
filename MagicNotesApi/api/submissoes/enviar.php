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

if(empty($data->atividade_id) || empty($data->usuario_id)){
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "atividade_id e usuario_id são obrigatórios"
    ]);
    exit;
}

try {
    // Verificar se já existe resposta
    $check_query = "SELECT id FROM atividades_respostas 
                    WHERE atividade_id = :atividade_id AND aluno_id = :usuario_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":atividade_id", $data->atividade_id);
    $check_stmt->bindParam(":usuario_id", $data->usuario_id);
    $check_stmt->execute();
    
    if($check_stmt->rowCount() > 0){
        // Atualizar resposta existente
        $query = "UPDATE atividades_respostas 
                  SET arquivo_url = :arquivo_url,
                      resposta = :resposta,
                      data_envio = NOW(),
                      status = 'enviada'
                  WHERE atividade_id = :atividade_id AND aluno_id = :usuario_id";
    } else {
        // Inserir nova resposta
        $query = "INSERT INTO atividades_respostas 
                  (atividade_id, aluno_id, arquivo_url, resposta, status, data_envio) 
                  VALUES (:atividade_id, :usuario_id, :arquivo_url, :resposta, 'enviada', NOW())";
    }
    
    $stmt = $db->prepare($query);
    
    $atividade_id = $data->atividade_id;
    $usuario_id = $data->usuario_id;
    $arquivo_url = isset($data->arquivo_url) ? $data->arquivo_url : null;
    $resposta = isset($data->resposta) ? $data->resposta : 'Arquivo enviado';
    
    $stmt->bindParam(":atividade_id", $atividade_id);
    $stmt->bindParam(":usuario_id", $usuario_id);
    $stmt->bindParam(":arquivo_url", $arquivo_url);
    $stmt->bindParam(":resposta", $resposta);
    
    if($stmt->execute()){
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Atividade enviada com sucesso"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Erro ao enviar atividade"
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