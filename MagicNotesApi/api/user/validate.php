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

if(empty($token)){
    http_response_code(401);
    echo json_encode(array(
        "success" => false,
        "message" => "Token não fornecido"
    ));
    exit;
}

try {
    // Decodificar e validar token
    $token_parts = explode('|', base64_decode($token));
    
    if(count($token_parts) < 3){
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Token inválido"
        ));
        exit;
    }
    
    $user_id = $token_parts[0];
    $timestamp = $token_parts[1];
    $email = $token_parts[2];
    
    // Verificar se o token não expirou (24 horas)
    $current_time = time();
    $token_age = $current_time - $timestamp;
    $max_age = 24 * 60 * 60; // 24 horas em segundos
    
    if($token_age > $max_age){
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Token expirado"
        ));
        exit;
    }
    
    // Buscar usuário no banco de dados
    $query = "SELECT 
                u.id, u.nome, u.email, u.telefone, u.foto_perfil, u.data_matricula, u.status,
                c.id as curso_id, c.nome as curso_nome, c.instrumento, c.nivel, c.carga_horaria
              FROM usuarios u
              LEFT JOIN cursos c ON u.curso_id = c.id
              WHERE u.id = :user_id AND u.email = :email AND u.status = 'ativo'";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if($stmt->rowCount() > 0){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response = array(
            "success" => true,
            "message" => "Token válido",
            "token" => $token,
            "user" => array(
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
            )
        );
        
        http_response_code(200);
        echo json_encode($response);
    } else {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Usuário não encontrado ou inativo"
        ));
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro ao validar token: " . $e->getMessage()
    ));
}
?>