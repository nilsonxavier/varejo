-- Otimizações de performance para o ERP
-- Execute estas queries no seu banco de dados

-- Índices para melhorar performance
CREATE INDEX IF NOT EXISTS idx_materiais_empresa ON materiais(empresa_id);
CREATE INDEX IF NOT EXISTS idx_clientes_empresa ON clientes(empresa_id);
CREATE INDEX IF NOT EXISTS idx_vendas_data ON vendas(data);
CREATE INDEX IF NOT EXISTS idx_vendas_cliente ON vendas(cliente_id);
CREATE INDEX IF NOT EXISTS idx_vendas_empresa ON vendas(empresa_id);
CREATE INDEX IF NOT EXISTS idx_compras_data ON compras(data_compra);
CREATE INDEX IF NOT EXISTS idx_compras_cliente ON compras(id_cliente);
CREATE INDEX IF NOT EXISTS idx_compras_empresa ON compras(empresa_id);
CREATE INDEX IF NOT EXISTS idx_estoque_material ON estoque(material_id);
CREATE INDEX IF NOT EXISTS idx_estoque_empresa ON estoque(empresa_id);
CREATE INDEX IF NOT EXISTS idx_estoque_data ON estoque(data_movimentacao);
CREATE INDEX IF NOT EXISTS idx_vendas_itens_venda ON vendas_itens(venda_id);
CREATE INDEX IF NOT EXISTS idx_vendas_itens_material ON vendas_itens(material_id);

-- Tabela de logs de auditoria (se não existir)
CREATE TABLE IF NOT EXISTS logs_auditoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_user (user_id),
    INDEX idx_logs_date (created_at)
);

-- Tabela de configurações de cache
CREATE TABLE IF NOT EXISTS cache_sistema (
    cache_key VARCHAR(255) PRIMARY KEY,
    cache_value LONGTEXT,
    expiry_time TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- View para relatórios de vendas otimizada
CREATE OR REPLACE VIEW vw_vendas_resumo AS
SELECT 
    v.id,
    v.data,
    c.nome as cliente_nome,
    SUM(vi.quantidade) as total_quantidade,
    SUM(vi.subtotal) as total_valor,
    COUNT(vi.id) as total_itens
FROM vendas v
LEFT JOIN clientes c ON v.cliente_id = c.id
LEFT JOIN vendas_itens vi ON v.id = vi.venda_id
GROUP BY v.id, v.data, c.nome;

-- Limpeza de dados antigos (comentado para segurança)
-- DELETE FROM logs_auditoria WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Estatísticas das tabelas
-- ANALYZE TABLE materiais, clientes, vendas, compras, estoque;
