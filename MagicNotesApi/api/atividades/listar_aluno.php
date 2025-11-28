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

try {
    // Query simplificada - lista TODAS as atividades ativas
    $query = "SELECT 
                a.id,
                a.titulo,
                a.descricao,
                a.data_entrega,
                a.pontuacao_maxima,
                d.nome AS disciplina,
                d.id AS disciplina_id,
                COALESCE(p.nome, 'Professor') AS professor,
                ar.id AS resposta_id,
                ar.status AS status_resposta,
                ar.arquivo_url AS arquivo_resposta,
                ar.nota,
                ar.feedback
              FROM atividades a
              INNER JOIN disciplinas d ON a.disciplina_id = d.id
              LEFT JOIN professores p ON a.professor_id = p.id
              LEFT JOIN atividades_respostas ar ON a.id = ar.atividade_id AND ar.aluno_id = :usuario_id
              WHERE a.ativo = TRUE
              ORDER BY a.data_entrega ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();

    $atividades = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $atividades[] = [
            "id" => (int)$row['id'],
            "titulo" => $row['titulo'],
            "descricao" => $row['descricao'] ?? 'Sem descrição',
            "disciplina" => $row['disciplina'],
            "disciplina_id" => (int)$row['disciplina_id'],
            "professor" => $row['professor'],
            "data_entrega" => $row['data_entrega'],
            "pontuacao_maxima" => (float)$row['pontuacao_maxima'],
            "respondida" => !empty($row['resposta_id']),
            "status_resposta" => $row['status_resposta'],
            "arquivo_resposta" => $row['arquivo_resposta'],
            "nota" => $row['nota'] ? (float)$row['nota'] : null,
            "feedback" => $row['feedback']
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