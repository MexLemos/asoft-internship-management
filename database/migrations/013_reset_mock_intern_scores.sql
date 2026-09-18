-- Migration: 013_reset_mock_intern_scores.sql
-- Resets any mock overall_score to 0.00 for interns who have not started any activities yet.

UPDATE interns i
LEFT JOIN (SELECT intern_id, COUNT(*) as cnt FROM attendance GROUP BY intern_id) a ON a.intern_id = i.id
LEFT JOIN (SELECT intern_id, COUNT(*) as cnt FROM task_assignments GROUP BY intern_id) t ON t.intern_id = i.id
SET i.overall_score = 0.00, i.risk_level = 'normal'
WHERE COALESCE(a.cnt, 0) = 0 AND COALESCE(t.cnt, 0) = 0;
