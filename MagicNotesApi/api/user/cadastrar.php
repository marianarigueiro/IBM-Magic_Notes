<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Tratamento para requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Validar campos obrigatórios
if(empty($data->nome) || empty($data->email) || empty($data->senha) || empty($data->curso_id)){
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Nome, email, senha e curso são obrigatórios"
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar formato do email
if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Email inválido"
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar tamanho mínimo da senha
if(strlen($data->senha) < 6){
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "A senha deve ter no mínimo 6 caracteres"
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Verificar se o email já existe
    $query_check = "SELECT id FROM usuarios WHERE email = :email";
    $stmt_check = $db->prepare($query_check);
    $stmt_check->bindParam(":email", $data->email);
    $stmt_check->execute();
    
    if($stmt_check->rowCount() > 0){
        http_response_code(409);
        echo json_encode(array(
            "success" => false,
            "message" => "Este email já está cadastrado"
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar se o curso existe
    $query_curso = "SELECT id FROM cursos WHERE id = :curso_id AND ativo = TRUE";
    $stmt_curso = $db->prepare($query_curso);
    $stmt_curso->bindParam(":curso_id", $data->curso_id);
    $stmt_curso->execute();
    
    if($stmt_curso->rowCount() == 0){
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Curso inválido ou inativo"
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Preparar dados para inserção
    $nome = trim($data->nome);
    $email = trim(strtolower($data->email));
    $senha_hash = md5($data->senha); // Use password_hash() em produção!
    $telefone = isset($data->telefone) ? trim($data->telefone) : null;
    $curso_id = (int)$data->curso_id;
    $data_matricula = isset($data->data_matricula) ? $data->data_matricula : date('Y-m-d');
    
    // Inserir novo usuário
    $query = "INSERT INTO usuarios (nome, email, senha, telefone, curso_id, data_matricula, status) 
              VALUES (:nome, :email, :senha, :telefone, :curso_id, :data_matricula, 'ativo')";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":nome", $nome);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":senha", $senha_hash);
    $stmt->bindParam(":telefone", $telefone);
    $stmt->bindParam(":curso_id", $curso_id);
    $stmt->bindParam(":data_matricula", $data_matricula);
    
    if($stmt->execute()){
        $user_id = $db->lastInsertId();
        
        // Buscar dados completos do usuário cadastrado
        $query_get = "SELECT 
                        u.id, u.nome, u.email, u.telefone, u.foto_perfil, u.data_matricula, u.status,
                        c.id as curso_id, c.nome as curso_nome, c.instrumento, c.nivel, c.carga_horaria
                      FROM usuarios u
                      LEFT JOIN cursos c ON u.curso_id = c.id
                      WHERE u.id = :user_id";
        
        $stmt_get = $db->prepare($query_get);
        $stmt_get->bindParam(":user_id", $user_id);
        $stmt_get->execute();
        
        $row = $stmt_get->fetch(PDO::FETCH_ASSOC);
        
        $response = array(
            "success" => true,
            "message" => "Aluno cadastrado com sucesso",
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
        
        http_response_code(201);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(array(
            "success" => false,
            "message" => "Erro ao cadastrar aluno"
        ), JSON_UNESCAPED_UNICODE);
    }
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro no banco de dados: " . $e->getMessage()
    ), JSON_UNESCAPED_UNICODE);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro ao processar requisição: " . $e->getMessage()
    ), JSON_UNESCAPED_UNICODE);
}
?>