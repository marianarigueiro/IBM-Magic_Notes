-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS magic_notes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE magic_notes;

-- Tabela de Cursos
CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    instrumento VARCHAR(50) NOT NULL,
    nivel ENUM('iniciante', 'intermediário', 'avançado') DEFAULT 'iniciante',
    carga_horaria INT DEFAULT 0,
    descricao TEXT,
    valor_mensalidade DECIMAL(10, 2) DEFAULT 0.00,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Usuários (Alunos)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    foto_perfil VARCHAR(255),
    curso_id INT,
    data_matricula DATE DEFAULT (CURRENT_DATE),
    status ENUM('ativo', 'inativo', 'suspenso') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Professores
CREATE TABLE IF NOT EXISTS professores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    disciplina VARCHAR(100),
    foto_perfil VARCHAR(255),
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Comunicados
CREATE TABLE IF NOT EXISTS comunicados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    conteudo TEXT NOT NULL,
    tipo ENUM('geral', 'urgente', 'informativo') DEFAULT 'geral',
    professor_id INT,
    data_publicacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Aulas Digitais
CREATE TABLE IF NOT EXISTS aulas_digitais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    link_reuniao VARCHAR(500) NOT NULL,
    data_inicio DATETIME NOT NULL,
    duracao_minutos INT DEFAULT 60,
    descricao TEXT,
    professor_id INT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Disciplinas (para alertas e atividades)
CREATE TABLE IF NOT EXISTS disciplinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    tipo ENUM('aula', 'teoria', 'historia', 'composicao', 'improvisacao', 'producao', 'pratica') DEFAULT 'aula',
    status ENUM('nao_feita', 'em_breve', 'feita', 'vencida') DEFAULT 'nao_feita',
    descricao TEXT,
    professor_id INT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Materiais Didáticos
CREATE TABLE IF NOT EXISTS materiais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    tipo ENUM('pdf', 'video', 'audio', 'link', 'apostila') NOT NULL,
    arquivo_url VARCHAR(500),
    curso VARCHAR(100),
    professor_id INT,
    tamanho_mb DECIMAL(10, 2),
    data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

USE magic_notes;

-- Tabela de Atividades
CREATE TABLE IF NOT EXISTS atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disciplina_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    data_entrega DATETIME NOT NULL,
    pontuacao_maxima DECIMAL(5, 2) DEFAULT 10.00,
    professor_id INT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Respostas dos Alunos
CREATE TABLE IF NOT EXISTS atividades_respostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT NOT NULL,
    aluno_id INT NOT NULL,
    resposta TEXT NOT NULL,
    arquivo_url VARCHAR(500),
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    nota DECIMAL(5, 2),
    feedback TEXT,
    status ENUM('pendente', 'enviada', 'corrigida') DEFAULT 'enviada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Eventos da Agenda
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    data DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    descricao TEXT,
    cor VARCHAR(20) DEFAULT '#93221F',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar curso_id em professores
ALTER TABLE professores ADD COLUMN curso_id INT NULL AFTER disciplina;
ALTER TABLE professores ADD FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL;

-- Adicionar curso_id nas tabelas de conteúdo
ALTER TABLE comunicados ADD COLUMN curso_id INT NULL AFTER professor_id;
ALTER TABLE aulas_digitais ADD COLUMN curso_id INT NULL AFTER professor_id;
ALTER TABLE materiais ADD COLUMN curso_id INT NULL AFTER professor_id;
ALTER TABLE disciplinas ADD COLUMN curso_id INT NULL AFTER professor_id;
ALTER TABLE atividades ADD COLUMN curso_id INT NULL AFTER professor_id;

-- Foreign keys
ALTER TABLE comunicados ADD FOREIGN KEY (curso_id) REFERENCES cursos(id);
ALTER TABLE aulas_digitais ADD FOREIGN KEY (curso_id) REFERENCES cursos(id);
ALTER TABLE materiais ADD FOREIGN KEY (curso_id) REFERENCES cursos(id);
ALTER TABLE disciplinas ADD FOREIGN KEY (curso_id) REFERENCES cursos(id);
ALTER TABLE atividades ADD FOREIGN KEY (curso_id) REFERENCES cursos(id);

-- Inserir cursos de exemplo
INSERT INTO cursos (nome, instrumento, nivel, carga_horaria, valor_mensalidade) VALUES
('Curso de Guitarra Iniciante', 'Guitarra', 'iniciante', 40, 250.00),
('Curso de Piano Intermediário', 'Piano', 'intermediário', 60, 300.00),
('Curso de Violão Avançado', 'Violão', 'avançado', 80, 350.00),
('Curso de Bateria Iniciante', 'Bateria', 'iniciante', 40, 280.00),
('Curso de Canto Popular', 'Canto', 'intermediário', 50, 320.00);

-- Inserir professor de exemplo (senha: prof123)
INSERT INTO professores (nome, email, senha, telefone, disciplina) VALUES
('Tereza Velasquez', 'tereza.velasquez@magicnotes.com', MD5('prof123'), '(11) 98765-1234', 'Coordenadora Pedagógica');

-- Inserir usuário de teste (senha: senha123)
INSERT INTO usuarios (nome, email, senha, telefone, curso_id, data_matricula) VALUES
('Carolina Martins Santos', 'carolina.santos@email.com', MD5('senha123'), '(11) 98765-4321', 1, '2024-01-15'),
('João Silva Oliveira', 'joao.silva@email.com', MD5('senha123'), '(11) 91234-5678', 2, '2024-02-20'),
('Maria Souza Lima', 'maria.lima@email.com', MD5('senha123'), '(11) 99876-5432', 3, '2024-03-10');

-- Inserir comunicados de exemplo
INSERT INTO comunicados (titulo, conteudo, tipo, professor_id) VALUES
('Evento Especial de Jazz!', 'No dia 18 de novembro de 2025, será realizado um encantador Recital de Jazz, celebrando a estação com música e apresentações especiais. Venha compartilhar momentos mágicos e alegres com nossa comunidade.', 'geral', 1),
('Convidada Especial: Ludmilla!', 'No dia 19 de maio de 2025, a artista renomada Ludmilla realizará uma aula especial em nossa escola de música. Não perca esta oportunidade de aprender com uma verdadeira estrela da música!', 'urgente', 1),
('Especial de Natal!', 'No dia 20 de dezembro de 2025, será realizado um delightful Recital de Natal, celebrando a estação com música e apresentações especiais. Venha compartilhar momentos mágicos e alegres com nossa comunidade.', 'informativo', 1);

-- Inserir aulas digitais de exemplo
INSERT INTO aulas_digitais (titulo, link_reuniao, data_inicio, duracao_minutos, descricao, professor_id) VALUES
('Aulas de Instrumentos', 'https://meet.google.com/aula-instrumentos', '2024-12-02 14:00:00', 60, 'Aula prática de instrumentos', 1),
('Teoria Musical e Percepção', 'https://meet.google.com/teoria-musical', '2024-12-04 14:00:00', 60, 'Fundamentos de teoria musical', 1),
('História da Música', 'https://meet.google.com/historia-musica', '2024-12-06 14:00:00', 60, 'História da música ocidental', 1),
('Composição e Arranjo', 'https://meet.google.com/composicao', '2024-12-09 14:00:00', 60, 'Técnicas de composição', 1),
('Improvisação', 'https://meet.google.com/improvisacao', '2024-12-11 14:00:00', 60, 'Exercícios de improvisação', 1),
('Produção Musical e Tecnologia', 'https://meet.google.com/producao', '2024-12-13 14:00:00', 60, 'Produção e gravação digital', 1),
('Prática em Conjunto', 'https://meet.google.com/pratica-conjunto', '2024-12-30 14:00:00', 90, 'Ensemble e prática de grupo (opcional)', 1);

-- Inserir disciplinas de exemplo
INSERT INTO disciplinas (nome, tipo, status, descricao, professor_id) VALUES
('Aulas de Instrumentos', 'aula', 'nao_feita', 'Aulas práticas de instrumento', 1),
('Teoria Musical e Percepção', 'teoria', 'vencida', 'Teoria musical básica', 1),
('História da Música', 'historia', 'nao_feita', 'História da música ocidental', 1),
('Aulas de Composição e Arranjo', 'composicao', 'nao_feita', 'Composição e harmonia', 1),
('Improvisação', 'improvisacao', 'vencida', 'Técnicas de improvisação', 1),
('Produção Musical e Tecnologia', 'producao', 'nao_feita', 'Produção e gravação', 1),
('Prática em Conjunto', 'pratica', 'nao_feita', 'Ensemble e prática de grupo', 1);

-- Inserir materiais de exemplo
INSERT INTO materiais (titulo, descricao, tipo, arquivo_url, curso, professor_id) VALUES
('Teoria Musical - Apostila 1', 'Fundamentos de teoria musical', 'apostila', 'https://drive.google.com/teoria-musical-1', 'Todos', 1),
('História da Música - Apostila 2', 'História da música ocidental', 'apostila', 'https://drive.google.com/historia-musica', 'Todos', 1),
('Harmonia Avançada - Apostila 3', 'Conceitos avançados de harmonia', 'apostila', 'https://drive.google.com/harmonia', 'Todos', 1);

-- Inserir atividades de exemplo
INSERT INTO atividades (disciplina_id, titulo, descricao, data_entrega, pontuacao_maxima, professor_id) VALUES
(1, 'Prática de Escala Maior', 'Pratique a escala maior em todas as tonalidades e grave um vídeo de 2 minutos', '2024-12-15 23:59:00', 10.00, 1),
(2, 'Exercício de Teoria - Intervalos', 'Complete os exercícios sobre intervalos musicais da apostila 1', '2024-12-10 23:59:00', 10.00, 1),
(3, 'Pesquisa sobre Bach', 'Faça uma pesquisa de 2 páginas sobre Johann Sebastian Bach', '2024-12-20 23:59:00', 10.00, 1),
(4, 'Composição de 8 compassos', 'Crie uma melodia original de 8 compassos', '2024-12-18 23:59:00', 10.00, 1),
(5, 'Improvisação sobre Blues', 'Improvise sobre uma base de blues em Dó', '2024-12-12 23:59:00', 10.00, 1);

-- Inserir alguns eventos de exemplo
INSERT INTO eventos (usuario_id, titulo, data, hora_inicio, hora_fim, descricao) VALUES
(1, 'Aula de Teoria Musical', '2024-12-05', '14:00:00', '15:00:00', 'Revisão de intervalos musicais'),
(1, 'Prática de Escalas', '2024-12-06', '10:00:00', '11:30:00', 'Praticar escalas maiores e menores'),
(1, 'Ensaio da Banda', '2024-12-07', '16:00:00', '18:00:00', 'Ensaio geral para apresentação');

-- Índices para melhor performance
CREATE INDEX idx_professor_email ON professores(email);
CREATE INDEX idx_usuario_email ON usuarios(email);
CREATE INDEX idx_comunicado_ativo ON comunicados(ativo);
CREATE INDEX idx_aula_ativo ON aulas_digitais(ativo);
CREATE INDEX idx_disciplina_ativo ON disciplinas(ativo);
CREATE INDEX idx_material_ativo ON materiais(ativo);
CREATE INDEX idx_atividade_disciplina ON atividades(disciplina_id);
CREATE INDEX idx_atividade_professor ON atividades(professor_id);
CREATE INDEX idx_resposta_atividade ON atividades_respostas(atividade_id);
CREATE INDEX idx_resposta_aluno ON atividades_respostas(aluno_id);
CREATE INDEX idx_resposta_status ON atividades_respostas(status);
CREATE INDEX idx_evento_usuario ON eventos(usuario_id);
CREATE INDEX idx_evento_data ON eventos(data);