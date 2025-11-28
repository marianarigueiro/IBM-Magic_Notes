<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Pegar token do header
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

// Validar token (simplificado)
if(empty($token)){
    http_response_code(401);
    echo json_encode(array("message" => "Token não fornecido"));
    exit;
}

// Extrair user_id do token
$token_parts = explode('|', base64_decode($token));
$user_id = $token_parts[0];

if(!empty($_GET['id'])){
    $user_id = $_GET['id'];
}

$query = "SELECT 
            u.id, u.nome, u.email, u.telefone, u.foto_perfil, u.data_matricula, u.status,
            c.id as curso_id, c.nome as curso_nome, c.instrumento, c.nivel, c.carga_horaria
          FROM usuarios u
          LEFT JOIN cursos c ON u.curso_id = c.id
          WHERE u.id = :user_id";

$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

if($stmt->rowCount() > 0){
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $user = array(
        "id" => (int)$row['id'],
        "nome" => $row['nome'],
        "email" => $row['email'],
        "telefone" => $row['telefone'],
        "foto_perfil" => $row['foto_perfil'],
        "data_matricula" => $row['data_matricula'],
        "status" => $row['status'],
        "curso" => array(
            "id" => (int)$row['curso_id'],
            "nome" => $row['curso_nome'],
            "instrumento" => $row['instrumento'],
            "nivel" => $row['nivel'],
            "carga_horaria" => (int)$row['carga_horaria']
        )
    );
    
    http_response_code(200);
    echo json_encode($user);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Usuário não encontrado"));
}
?>