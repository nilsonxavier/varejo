<?php
/**
 * Funções auxiliares para o Sistema ERP
 * Centraliza operações comuns e utilitários
 */

class ERPFunctions {
    
    /**
     * Formata moeda brasileira
     */
    public static function formatCurrency($value) {
        return "R$ " . number_format(floatval($value), 2, ",", ".");
    }
    
    /**
     * Formata peso (kg)
     */
    public static function formatWeight($value) {
        return number_format(floatval($value), 3, ",", ".") . " kg";
    }
    
    /**
     * Formata data brasileira
     */
    public static function formatDate($date) {
        return date("d/m/Y", strtotime($date));
    }
    
    /**
     * Formata data e hora brasileira  
     */
    public static function formatDateTime($datetime) {
        return date("d/m/Y H:i", strtotime($datetime));
    }
    
    /**
     * Gera código único para lotes
     */
    public static function generateLoteCode($material_id, $empresa_id) {
        $prefix = "L" . str_pad($empresa_id, 2, "0", STR_PAD_LEFT);
        $suffix = str_pad($material_id, 3, "0", STR_PAD_LEFT);
        $timestamp = date("ymd");
        $random = str_pad(rand(1, 999), 3, "0", STR_PAD_LEFT);
        
        return $prefix . $timestamp . $suffix . $random;
    }
    
    /**
     * Calcula peso líquido (bruto - tara)
     */
    public static function calculatePesoLiquido($peso_bruto, $tara = 0) {
        return max(0, floatval($peso_bruto) - floatval($tara));
    }
    
    /**
     * Valida e formata telefone
     */
    public static function formatPhone($phone) {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        
        if (strlen($phone) == 11) {
            return preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", $phone);
        } elseif (strlen($phone) == 10) {
            return preg_replace("/(\d{2})(\d{4})(\d{4})/", "($1) $2-$3", $phone);
        }
        
        return $phone;
    }
    
    /**
     * Gera hash seguro para senhas
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    /**
     * Verifica senha contra hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Cache simples de dados
     */
    public static function cache($key, $data = null, $ttl = 3600) {
        global $conn;
        
        if ($data === null) {
            // Buscar do cache
            $stmt = $conn->prepare("SELECT cache_value FROM cache_sistema WHERE cache_key = ? AND expiry_time > NOW()");
            $stmt->bind_param("s", $key);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return unserialize($row["cache_value"]);
            }
            return false;
        } else {
            // Salvar no cache
            $expiry = date("Y-m-d H:i:s", time() + $ttl);
            $serialized_data = serialize($data);
            
            $stmt = $conn->prepare("REPLACE INTO cache_sistema (cache_key, cache_value, expiry_time) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $key, $serialized_data, $expiry);
            return $stmt->execute();
        }
    }
    
    /**
     * Limpa cache expirado
     */
    public static function clearExpiredCache() {
        global $conn;
        $conn->query("DELETE FROM cache_sistema WHERE expiry_time < NOW()");
    }
    
    /**
     * Gera relatório de materiais mais vendidos
     */
    public static function getMaterialsMaisVendidos($empresa_id, $limit = 10) {
        global $conn;
        
        $cache_key = "materiais_mais_vendidos_" . $empresa_id;
        $cached = self::cache($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $stmt = $conn->prepare("
            SELECT m.nome, SUM(vi.quantidade) as total_vendido, SUM(vi.subtotal) as total_valor
            FROM materiais m
            JOIN vendas_itens vi ON m.id = vi.material_id
            JOIN vendas v ON vi.venda_id = v.id
            WHERE m.empresa_id = ? 
            AND v.data_venda >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY m.id, m.nome
            ORDER BY total_vendido DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $empresa_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $materials = [];
        while ($row = $result->fetch_assoc()) {
            $materials[] = $row;
        }
        
        // Cache por 1 hora
        self::cache($cache_key, $materials, 3600);
        
        return $materials;
    }
}
?>