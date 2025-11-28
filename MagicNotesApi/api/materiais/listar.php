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
        $query = "SELECT m.id, m.titulo, m.descricao, m.tipo, m.arquivo_url, 
                         m.curso, m.data_upload,
                         p.nome as professor_nome, p.disciplina
                  FROM materiais m
                  LEFT JOIN professores p ON m.professor_id = p.id
                  INNER JOIN usuarios u ON u.id = :usuario_id
                  WHERE m.ativo = TRUE AND m.curso_id = u.curso_id
                  ORDER BY m.data_upload DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
    } else {
        $query = "SELECT m.id, m.titulo, m.descricao, m.tipo, m.arquivo_url, 
                         m.curso, m.data_upload,
                         p.nome as professor_nome, p.disciplina
                  FROM materiais m
                  LEFT JOIN professores p ON m.professor_id = p.id
                  WHERE m.ativo = TRUE
                  ORDER BY m.data_upload DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
    }
    
    $materiais = array();
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $material = array(
            "id" => (int)$row['id'],
            "titulo" => $row['titulo'],
            "descricao" => $row['descricao'],
            "tipo" => $row['tipo'],
            "arquivo_url" => $row['arquivo_url'],
            "curso" => $row['curso'],
            "data_upload" => $row['data_upload'],
            "professor" => array(
                "nome" => $row['professor_nome'],
                "disciplina" => $row['disciplina']
            )
        );
        
        array_push($materiais, $material);
    }
    
    http_response_code(200);
    echo json_encode($materiais, JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
    ));
}
?>