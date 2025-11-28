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

if(empty($data->nome) || empty($data->email) || empty($data->senha) || empty($data->disciplina) || empty($data->curso_id)){
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Nome, email, senha, disciplina e curso são obrigatórios"
    ));
    exit;
}

try {
    $query_check = "SELECT id FROM professores WHERE email = :email";
    $stmt_check = $db->prepare($query_check);
    $stmt_check->bindParam(":email", $data->email);
    $stmt_check->execute();
    
    if($stmt_check->rowCount() > 0){
        http_response_code(409);
        echo json_encode(array(
            "success" => false,
            "message" => "Este email já está cadastrado"
        ));
        exit;
    }
    
    $nome = trim($data->nome);
    $email = trim(strtolower($data->email));
    $senha_hash = md5($data->senha);
    $telefone = isset($data->telefone) ? trim($data->telefone) : null;
    $disciplina = trim($data->disciplina);
    $curso_id = (int)$data->curso_id;
    
    $query = "INSERT INTO professores (nome, email, senha, telefone, disciplina, curso_id, status) 
              VALUES (:nome, :email, :senha, :telefone, :disciplina, :curso_id, 'ativo')";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":nome", $nome);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":senha", $senha_hash);
    $stmt->bindParam(":telefone", $telefone);
    $stmt->bindParam(":disciplina", $disciplina);
    $stmt->bindParam(":curso_id", $curso_id);
    
    if($stmt->execute()){
        http_response_code(201);
        echo json_encode(array(
            "success" => true,
            "message" => "Professor cadastrado com sucesso"
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "success" => false,
            "message" => "Erro ao cadastrar professor"
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