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

if(empty($data->titulo) || empty($data->link_reuniao) || empty($data->data_inicio) || empty($data->professor_id)){
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Título, link, data e professor_id são obrigatórios"
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
    
    $query = "INSERT INTO aulas_digitais (titulo, link_reuniao, data_inicio, duracao_minutos, descricao, professor_id, curso_id) 
              VALUES (:titulo, :link_reuniao, :data_inicio, :duracao_minutos, :descricao, :professor_id, :curso_id)";
    
    $stmt = $db->prepare($query);
    
    $titulo = trim($data->titulo);
    $link_reuniao = trim($data->link_reuniao);
    $data_inicio = $data->data_inicio;
    $duracao_minutos = isset($data->duracao_minutos) ? $data->duracao_minutos : 60;
    $descricao = isset($data->descricao) ? trim($data->descricao) : null;
    $professor_id = $data->professor_id;
    
    $stmt->bindParam(":titulo", $titulo);
    $stmt->bindParam(":link_reuniao", $link_reuniao);
    $stmt->bindParam(":data_inicio", $data_inicio);
    $stmt->bindParam(":duracao_minutos", $duracao_minutos);
    $stmt->bindParam(":descricao", $descricao);
    $stmt->bindParam(":professor_id", $professor_id);
    $stmt->bindParam(":curso_id", $curso_id);
    
    if($stmt->execute()){
        http_response_code(201);
        echo json_encode(array(
            "success" => true,
            "message" => "Aula criada com sucesso"
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "success" => false,
            "message" => "Erro ao criar aula"
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