<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Competency
{
    public static function ensureDefaultCompetenciesExist(): void
    {
        try {
            $pdo = Database::getConnection();
            $count = (int)$pdo->query("SELECT COUNT(*) FROM competencies")->fetchColumn();
            if ($count > 0) {
                return;
            }

            // Insert default categories if missing
            $stmtCat = $pdo->prepare("INSERT INTO competency_categories (name, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = VALUES(description)");
            $stmtCat->execute(['Técnicas', 'Conhecimentos práticos e domínio de ferramentas tecnológicas']);
            $catTecId = (int)$pdo->lastInsertId() ?: (int)$pdo->query("SELECT id FROM competency_categories WHERE name = 'Técnicas'")->fetchColumn();

            $stmtCat->execute(['Comportamentais', 'Habilidades interpessoais, postura profissional e atitude']);
            $catCompId = (int)$pdo->lastInsertId() ?: (int)$pdo->query("SELECT id FROM competency_categories WHERE name = 'Comportamentais'")->fetchColumn();

            $competencies = [
                [$catTecId, 'Programação PHP & MySQL', 'Capacidade de desenvolver lógicas orientadas a objetos, queries seguras e MVC.', 1.20],
                [$catTecId, 'Frontend (HTML5, CSS3, JS & Bootstrap)', 'Criação de layouts responsivos, manipulação de DOM e Fetch API.', 1.00],
                [$catTecId, 'Redes & Subnetting', 'Compreensão de topologias, cálculo de sub-redes e configuração de switches.', 1.00],
                [$catTecId, 'Git & Versionamento', 'Fluxo de trabalho com branches, commits atômicos e Pull Requests.', 1.00],
                [$catTecId, 'Bases de Dados & SQL', 'Normalização, relacionamentos e criação de índices eficazes.', 1.10],
                [$catCompId, 'Trabalho em Equipe', 'Colaboração ativa, respeito e partilha de conhecimento com colegas.', 1.00],
                [$catCompId, 'Comunicação Clara', 'Expressão oral e escrita objetiva, reportes concisos aos supervisores.', 1.00],
                [$catCompId, 'Proatividade & Autonomia', 'Iniciativa para resolver problemas e propor melhorias sem esperar ordens.', 1.10],
                [$catCompId, 'Pontualidade & Compromisso', 'Cumprimento rigoroso de horários de presença e prazos de tarefas.', 1.00],
                [$catCompId, 'Resolução de Problemas', 'Capacidade analítica para investigar causas-raiz e debugar erros.', 1.20],
            ];

            $stmt = $pdo->prepare("INSERT INTO competencies (category_id, name, description, default_weight) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE description = VALUES(description)");
            foreach ($competencies as $comp) {
                $stmt->execute($comp);
            }
        } catch (\Throwable $e) {
            // Graceful fallback
        }
    }

    public static function allWithCategories(): array
    {
        self::ensureDefaultCompetenciesExist();
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT c.*, cat.name as category_name
            FROM competencies c
            INNER JOIN competency_categories cat ON cat.id = c.category_id
            ORDER BY cat.name ASC, c.name ASC
        ");
        return $stmt->fetchAll();
    }

    public static function getForIntern(int $internId): array
    {
        self::ensureDefaultCompetenciesExist();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, cat.name as category_name, 
                    COALESCE(ic.current_level, 1) as current_level,
                    ic.evidence_notes,
                    ic.evaluated_at,
                    u.name as evaluator_name
            FROM competencies c
            INNER JOIN competency_categories cat ON cat.id = c.category_id
            LEFT JOIN intern_competencies ic ON ic.competency_id = c.id AND ic.intern_id = ?
            LEFT JOIN users u ON u.id = ic.evaluated_by
            ORDER BY cat.name ASC, c.name ASC
        ");
        $stmt->execute([$internId]);
        return $stmt->fetchAll();
    }

    public static function evaluate(int $internId, int $competencyId, int $level, int $evaluatorId, ?string $notes): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO intern_competencies (intern_id, competency_id, current_level, evaluated_by, evidence_notes, evaluated_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                current_level = VALUES(current_level),
                evaluated_by = VALUES(evaluated_by),
                evidence_notes = VALUES(evidence_notes),
                evaluated_at = NOW()
        ");
        return $stmt->execute([$internId, $competencyId, $level, $evaluatorId, $notes]);
    }
}
