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

if (!isset($_GET['professor_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "professor_id obrigatório"]);
    exit();
}

$professor_id = intval($_GET['professor_id']);

try {
    $query = "SELECT 
                a.id,
                a.titulo,
                a.descricao,
                a.data_entrega,
                a.pontuacao_maxima,
                a.arquivo_url,
                d.nome AS disciplina_nome,
                d.id AS disciplina_id,
                COUNT(ar.id) AS total_respostas,
                SUM(CASE WHEN ar.status = 'enviada' THEN 1 ELSE 0 END) AS respostas_pendentes,
                SUM(CASE WHEN ar.status = 'corrigida' THEN 1 ELSE 0 END) AS respostas_corrigidas
              FROM atividades a
              INNER JOIN disciplinas d ON a.disciplina_id = d.id
              LEFT JOIN atividades_respostas ar ON a.id = ar.atividade_id
              WHERE a.professor_id = :professor_id AND a.ativo = TRUE
              GROUP BY a.id
              ORDER BY a.data_entrega DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':professor_id', $professor_id, PDO::PARAM_INT);
    $stmt->execute();

    $atividades = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $atividades[] = [
            "id" => (int)$row['id'],
            "titulo" => $row['titulo'],
            "descricao" => $row['descricao'],
            "data_entrega" => $row['data_entrega'],
            "pontuacao_maxima" => (float)$row['pontuacao_maxima'],
            "arquivo_url" => $row['arquivo_url'],
            "disciplina_nome" => $row['disciplina_nome'],
            "disciplina_id" => (int)$row['disciplina_id'],
            "total_respostas" => (int)$row['total_respostas'],
            "respostas_pendentes" => (int)$row['respostas_pendentes'],
            "respostas_corrigidas" => (int)$row['respostas_corrigidas']
        ];
    }

    http_response_code(200);
    echo json_encode($atividades, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>