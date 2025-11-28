<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['usuario_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "usuario_id obrigatório"]);
    exit();
}

$usuario_id = intval($_GET['usuario_id']);

// Filtro opcional por mês/ano
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : null;
$ano = isset($_GET['ano']) ? intval($_GET['ano']) : null;

try {
    $query = "SELECT id, titulo, data, hora_inicio, hora_fim, descricao, cor
              FROM eventos 
              WHERE usuario_id = :usuario_id";
    
    if ($mes && $ano) {
        $query .= " AND MONTH(data) = :mes AND YEAR(data) = :ano";
    }
    
    $query .= " ORDER BY data ASC, hora_inicio ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    
    if ($mes && $ano) {
        $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
    }
    
    $stmt->execute();

    $eventos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $eventos[] = [
            "id" => (int)$row['id'],
            "titulo" => $row['titulo'],
            "data" => $row['data'],
            "hora_inicio" => substr($row['hora_inicio'], 0, 5), // HH:MM
            "hora_fim" => substr($row['hora_fim'], 0, 5), // HH:MM
            "descricao" => $row['descricao'],
            "cor" => $row['cor']
        ];
    }

    http_response_code(200);
    echo json_encode($eventos, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>