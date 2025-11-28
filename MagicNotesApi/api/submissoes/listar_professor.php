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

if (!isset($_GET['atividade_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "atividade_id obrigatório"]);
    exit();
}

$atividade_id = intval($_GET['atividade_id']);

try {
    $query = "SELECT 
                ar.id,
                ar.resposta,
                ar.arquivo_url,
                ar.data_envio,
                ar.nota,
                ar.feedback,
                ar.status,
                u.nome AS aluno_nome,
                u.email AS aluno_email,
                u.id AS aluno_id,
                a.titulo AS atividade_titulo,
                a.pontuacao_maxima
              FROM atividades_respostas ar
              INNER JOIN usuarios u ON ar.aluno_id = u.id
              INNER JOIN atividades a ON ar.atividade_id = a.id
              WHERE ar.atividade_id = :atividade_id
              ORDER BY ar.data_envio DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':atividade_id', $atividade_id, PDO::PARAM_INT);
    $stmt->execute();

    $submissoes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $submissoes[] = [
            "id" => (int)$row['id'],
            "resposta" => $row['resposta'],
            "arquivo_url" => $row['arquivo_url'],
            "data_envio" => $row['data_envio'],
            "nota" => $row['nota'] ? (float)$row['nota'] : null,
            "feedback" => $row['feedback'],
            "status" => $row['status'],
            "aluno_nome" => $row['aluno_nome'],
            "aluno_email" => $row['aluno_email'],
            "aluno_id" => (int)$row['aluno_id'],
            "atividade_titulo" => $row['atividade_titulo'],
            "pontuacao_maxima" => (float)$row['pontuacao_maxima']
        ];
    }

    http_response_code(200);
    echo json_encode($submissoes, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>