<?php
/**
 * Funções de segurança para o ERP
 * Implementa validações e sanitizações básicas
 */

class ERPSecurity {
    
    /**
     * Sanitiza entrada de texto
     */
    public static function sanitizeInput($input, $type = "string") {
        if (is_array($input)) {
            return array_map([self::class, "sanitizeInput"], $input);
        }
        
        $input = trim($input);
        
        switch ($type) {
            case "email":
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case "int":
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case "float":
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case "url":
                return filter_var($input, FILTER_SANITIZE_URL);
            default:
                return htmlspecialchars($input, ENT_QUOTES, "UTF-8");
        }
    }
    
    /**
     * Valida CNPJ
     */
    public static function validateCNPJ($cnpj) {
        $cnpj = preg_replace("/[^0-9]/", "", $cnpj);
        if (strlen($cnpj) != 14) return false;
        
        // Validação algoritmo CNPJ
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) return false;
        
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
    
    /**
     * Valida CPF
     */
    public static function validateCPF($cpf) {
        $cpf = preg_replace("/[^0-9]/", "", $cpf);
        if (strlen($cpf) != 11) return false;
        if (preg_match("/^(\d)\1{10}$/", $cpf)) return false;
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }
    
    /**
     * Log de auditoria melhorado
     */
    public static function logAction($user_id, $action, $details = "", $ip = null) {
        global $conn;
        
        if (!$ip) {
            $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
        }
        
        $stmt = $conn->prepare("INSERT INTO logs_auditoria (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $stmt->execute();
    }
    
    /**
     * Verifica força da senha
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Senha deve ter pelo menos 8 caracteres";
        }
        if (!preg_match("/[a-z]/", $password)) {
            $errors[] = "Senha deve conter pelo menos uma letra minúscula";
        }
        if (!preg_match("/[A-Z]/", $password)) {
            $errors[] = "Senha deve conter pelo menos uma letra maiúscula";
        }
        if (!preg_match("/\d/", $password)) {
            $errors[] = "Senha deve conter pelo menos um número";
        }
        
        return empty($errors) ? true : $errors;
    }
}
?>