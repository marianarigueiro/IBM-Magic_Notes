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

if(empty($data->titulo) || empty($data->conteudo) || empty($data->professor_id)){
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Título, conteúdo e professor_id são obrigatórios"
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
    
    $query = "INSERT INTO comunicados (titulo, conteudo, tipo, professor_id, curso_id) 
              VALUES (:titulo, :conteudo, :tipo, :professor_id, :curso_id)";
    
    $stmt = $db->prepare($query);
    
    $titulo = trim($data->titulo);
    $conteudo = trim($data->conteudo);
    $tipo = isset($data->tipo) ? $data->tipo : 'geral';
    $professor_id = $data->professor_id;
    
    $stmt->bindParam(":titulo", $titulo);
    $stmt->bindParam(":conteudo", $conteudo);
    $stmt->bindParam(":tipo", $tipo);
    $stmt->bindParam(":professor_id", $professor_id);
    $stmt->bindParam(":curso_id", $curso_id);
    
    if($stmt->execute()){
        http_response_code(201);
        echo json_encode(array(
            "success" => true,
            "message" => "Comunicado publicado com sucesso"
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "success" => false,
            "message" => "Erro ao publicar comunicado"
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