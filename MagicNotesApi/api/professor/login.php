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

if(empty($data->email) || empty($data->senha)){
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Email e senha são obrigatórios"
    ));
    exit;
}

try {
    $query = "SELECT p.id, p.nome, p.email, p.telefone, p.disciplina, p.curso_id, p.status,
                     c.nome as curso_nome, c.instrumento
              FROM professores p
              LEFT JOIN cursos c ON p.curso_id = c.id
              WHERE p.email = :email AND p.senha = :senha AND p.status = 'ativo'";
    
    $stmt = $db->prepare($query);
    
    $email = trim(strtolower($data->email));
    $senha_hash = md5($data->senha);
    
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":senha", $senha_hash);
    
    $stmt->execute();
    
    if($stmt->rowCount() > 0){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $token = base64_encode($row['id'] . "|" . time() . "|" . $row['email'] . "|professor");
        
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Login realizado com sucesso",
            "token" => $token,
            "professor" => array(
                "id" => (int)$row['id'],
                "nome" => $row['nome'],
                "email" => $row['email'],
                "telefone" => $row['telefone'],
                "disciplina" => $row['disciplina'],
                "curso_id" => (int)$row['curso_id'],
                "curso_nome" => $row['curso_nome'],
                "instrumento" => $row['instrumento'],
                "status" => $row['status']
            )
        ));
    } else {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Email ou senha incorretos"
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