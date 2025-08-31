-- Migração: adiciona coluna `padrao` em `listas_precos`
ALTER TABLE listas_precos
  ADD COLUMN padrao TINYINT(1) NOT NULL DEFAULT 0;

-- Opcional: marca como padrao a primeira lista encontrada por empresa (executar manualmente se desejar)
-- UPDATE listas_precos lp
-- JOIN (
--   SELECT empresa_id, MIN(id) as id FROM listas_precos GROUP BY empresa_id
-- ) t ON lp.empresa_id = t.empresa_id AND lp.id = t.id
-- SET lp.padrao = 1;
