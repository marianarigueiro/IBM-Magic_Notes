<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;

try {
    if($usuario_id){
        $query = "SELECT a.id, a.titulo, a.link_reuniao, a.data_inicio, 
                         a.duracao_minutos, a.descricao,
                         COALESCE(p.nome, 'Professor') as professor_nome, 
                         COALESCE(p.disciplina, 'Música') as disciplina
                  FROM aulas_digitais a
                  LEFT JOIN professores p ON a.professor_id = p.id
                  INNER JOIN usuarios u ON u.id = :usuario_id
                  WHERE a.ativo = TRUE AND a.curso_id = u.curso_id
                  ORDER BY a.data_inicio DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
    } else {
        $query = "SELECT a.id, a.titulo, a.link_reuniao, a.data_inicio, 
                         a.duracao_minutos, a.descricao,
                         COALESCE(p.nome, 'Professor') as professor_nome, 
                         COALESCE(p.disciplina, 'Música') as disciplina
                  FROM aulas_digitais a
                  LEFT JOIN professores p ON a.professor_id = p.id
                  WHERE a.ativo = TRUE
                  ORDER BY a.data_inicio DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
    }
    
    $aulas = array();
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $aula = array(
            "id" => (int)$row['id'],
            "titulo" => $row['titulo'],
            "link_reuniao" => $row['link_reuniao'],
            "data_inicio" => $row['data_inicio'],
            "duracao_minutos" => (int)$row['duracao_minutos'],
            "descricao" => $row['descricao'],
            "professor" => array(
                "nome" => $row['professor_nome'],
                "disciplina" => $row['disciplina']
            )
        );
        
        array_push($aulas, $aula);
    }
    
    http_response_code(200);
    echo json_encode($aulas, JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
    ));
}
?>