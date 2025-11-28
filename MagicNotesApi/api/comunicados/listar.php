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
        // Filtrar por curso do aluno
        $query = "SELECT c.id, c.titulo, c.conteudo, c.tipo, c.data_publicacao,
                         COALESCE(p.nome, 'Coordenação') as professor_nome, 
                         COALESCE(p.disciplina, 'Coordenadora Pedagógica') as disciplina
                  FROM comunicados c
                  LEFT JOIN professores p ON c.professor_id = p.id
                  INNER JOIN usuarios u ON u.id = :usuario_id
                  WHERE c.ativo = TRUE AND c.curso_id = u.curso_id
                  ORDER BY c.data_publicacao DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
    } else {
        // Listar todos
        $query = "SELECT c.id, c.titulo, c.conteudo, c.tipo, c.data_publicacao,
                         COALESCE(p.nome, 'Coordenação') as professor_nome, 
                         COALESCE(p.disciplina, 'Coordenadora Pedagógica') as disciplina
                  FROM comunicados c
                  LEFT JOIN professores p ON c.professor_id = p.id
                  WHERE c.ativo = TRUE
                  ORDER BY c.data_publicacao DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
    }
    
    $comunicados = array();
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $comunicado = array(
            "id" => (int)$row['id'],
            "titulo" => $row['titulo'],
            "conteudo" => $row['conteudo'],
            "tipo" => $row['tipo'],
            "data_publicacao" => $row['data_publicacao'],
            "professor" => array(
                "nome" => $row['professor_nome'],
                "disciplina" => $row['disciplina']
            )
        );
        
        array_push($comunicados, $comunicado);
    }
    
    http_response_code(200);
    echo json_encode($comunicados, JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
    ));
}
?>