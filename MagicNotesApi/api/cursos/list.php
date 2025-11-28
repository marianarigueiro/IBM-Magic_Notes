<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Tratamento para requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Falha na conexão com o banco de dados");
    }
    
    // Buscar todos os cursos ativos
    $query = "SELECT id, nome, instrumento, nivel, carga_horaria, descricao, valor_mensalidade 
              FROM cursos 
              WHERE ativo = TRUE 
              ORDER BY nome ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $cursos = array();
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $curso = array(
            "id" => (int)$row['id'],
            "nome" => $row['nome'],
            "instrumento" => $row['instrumento'],
            "nivel" => $row['nivel'],
            "carga_horaria" => (int)$row['carga_horaria'],
            "descricao" => $row['descricao'] ?? '',
            "valor_mensalidade" => number_format((float)$row['valor_mensalidade'], 2, '.', '')
        );
        
        array_push($cursos, $curso);
    }
    
    // Retornar array vazio se não houver cursos
    http_response_code(200);
    echo json_encode($cursos, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro de banco de dados: " . $e->getMessage()
    ), JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro ao buscar cursos: " . $e->getMessage()
    ), JSON_UNESCAPED_UNICODE);
}
?>