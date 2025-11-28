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

if(empty($data->nome) || empty($data->professor_id)){
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Nome da disciplina e professor_id são obrigatórios"
    ));
    exit;
}

try {
    // Buscar curso_id do professor
    $query_prof = "SELECT curso_id FROM professores WHERE id = :professor_id";
    $stmt_prof = $db->prepare($query_prof);
    $stmt_prof->bindParam(":professor_id", $data->professor_id);
    $stmt_prof->execute();
    
    if($stmt_prof->rowCount() == 0){
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "Professor não encontrado"));
        exit;
    }
    
    $curso_id = $stmt_prof->fetchColumn();
    
    $query = "INSERT INTO disciplinas (nome, tipo, status, descricao, professor_id, curso_id) 
              VALUES (:nome, :tipo, :status, :descricao, :professor_id, :curso_id)";
    
    $stmt = $db->prepare($query);
    
    $nome = trim($data->nome);
    $tipo = isset($data->tipo) ? $data->tipo : 'aula';
    $status = isset($data->status) ? $data->status : 'nao_feita';
    $descricao = isset($data->descricao) ? trim($data->descricao) : null;
    $professor_id = $data->professor_id;
    
    $stmt->bindParam(":nome", $nome);
    $stmt->bindParam(":tipo", $tipo);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":descricao", $descricao);
    $stmt->bindParam(":professor_id", $professor_id);
    $stmt->bindParam(":curso_id", $curso_id);
    
    if($stmt->execute()){
        http_response_code(201);
        echo json_encode(array(
            "success" => true,
            "message" => "Disciplina criada com sucesso"
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "success" => false,
            "message" => "Erro ao criar disciplina"
        ));
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
    ));
}
?>