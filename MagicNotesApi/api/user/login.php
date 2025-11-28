<?php
ini_set('display_errors', 0);
error_reporting(0);

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Tratamento para requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Certifique-se que database.php não imprime nada
include_once '../../config/database.php';

// Função alternativa para getallheaders (caso não exista)
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

try {
    // Conexão
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Falha na conexão com o banco de dados");
    }

    // Receber dados
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput);
    
    // Log para debug (remova em produção)
    error_log("Login attempt - Raw input: " . $rawInput);

    // Validar
    if(empty($data->email) || empty($data->senha)){
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Email e senha são obrigatórios"
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $query = "SELECT 
                u.id, u.nome, u.email, u.telefone, u.foto_perfil, u.data_matricula, u.status,
                c.id as curso_id, c.nome as curso_nome, c.instrumento, c.nivel, c.carga_horaria
              FROM usuarios u
              LEFT JOIN cursos c ON u.curso_id = c.id
              WHERE u.email = :email AND u.senha = :senha AND u.status = 'ativo'";

    $stmt = $db->prepare($query);

    // MD5 para teste (troque para password_hash/verify em produção)
    $email = trim(strtolower($data->email));
    $senha_hash = md5($data->senha);

    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":senha", $senha_hash);

    $stmt->execute();

    if($stmt->rowCount() > 0){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Token simplificado
        $token = base64_encode($row['id'] . "|" . time() . "|" . $row['email']);

        $response = array(
            "success" => true,
            "message" => "Login realizado com sucesso",
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
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;

    } else {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Email ou senha incorretos"
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro no banco de dados"
    ), JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro ao processar login: " . $e->getMessage()
    ), JSON_UNESCAPED_UNICODE);
    exit;
}
?>