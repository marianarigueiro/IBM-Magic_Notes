<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Validar token
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

if(empty($token)){
    http_response_code(401);
    echo json_encode(array("message" => "Token não fornecido"));
    exit;
}

if(!empty($data->id) && !empty($data->nome) && !empty($data->email)){
    
    $query = "UPDATE usuarios 
              SET nome = :nome, 
                  email = :email, 
                  telefone = :telefone
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":nome", $data->nome);
    $stmt->bindParam(":email", $data->email);
    $stmt->bindParam(":telefone", $data->telefone);
    $stmt->bindParam(":id", $data->id);
    
    if($stmt->execute()){
        // Buscar dados atualizados
        $query_get = "SELECT 
                        u.id, u.nome, u.email, u.telefone, u.foto_perfil, u.data_matricula, u.status,
                        c.id as curso_id, c.nome as curso_nome, c.instrumento, c.nivel, c.carga_horaria
                      FROM usuarios u
                      LEFT JOIN cursos c ON u.curso_id = c.id
                      WHERE u.id = :id";
        
        $stmt_get = $db->prepare($query_get);
        $stmt_get->bindParam(":id", $data->id);
        $stmt_get->execute();
        
        $row = $stmt_get->fetch(PDO::FETCH_ASSOC);
        
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
        http_response_code(500);
        echo json_encode(array("message" => "Erro ao atualizar usuário"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Dados incompletos"));
}
?>