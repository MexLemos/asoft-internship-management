-- Migration: 016_seed_default_competencies.sql
-- Inserir categorias e competências padrão no sistema caso estejam vazias

INSERT INTO competency_categories (id, name, description)
VALUES 
    (1, 'Técnicas', 'Conhecimentos práticos e domínio de ferramentas tecnológicas'),
    (2, 'Comportamentais', 'Habilidades interpessoais, postura profissional e atitude')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO competencies (category_id, name, description, default_weight)
VALUES
    (1, 'Programação PHP & MySQL', 'Capacidade de desenvolver lógicas orientadas a objetos, queries seguras e MVC.', 1.20),
    (1, 'Frontend (HTML5, CSS3, JS & Bootstrap)', 'Criação de layouts responsivos, manipulação de DOM e Fetch API.', 1.00),
    (1, 'Redes & Subnetting', 'Compreensão de topologias, cálculo de sub-redes e configuração de switches.', 1.00),
    (1, 'Git & Versionamento', 'Fluxo de trabalho com branches, commits atômicos e Pull Requests.', 1.00),
    (1, 'Bases de Dados & SQL', 'Normalização, relacionamentos e criação de índices eficazes.', 1.10),
    (2, 'Trabalho em Equipe', 'Colaboração ativa, respeito e partilha de conhecimento com colegas.', 1.00),
    (2, 'Comunicação Clara', 'Expressão oral e escrita objetiva, reportes concisos aos supervisores.', 1.00),
    (2, 'Proatividade & Autonomia', 'Iniciativa para resolver problemas e propor melhorias sem esperar ordens.', 1.10),
    (2, 'Pontualidade & Compromisso', 'Cumprimento rigoroso de horários de presença e prazos de tarefas.', 1.00),
    (2, 'Resolução de Problemas', 'Capacidade analítica para investigar causas-raiz e debugar erros.', 1.20)
ON DUPLICATE KEY UPDATE description = VALUES(description);
