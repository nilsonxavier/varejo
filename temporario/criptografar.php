<?php
require_once 'C:\wamp64\www\varejo\varejo\conexx\config.php';

$sql = "SELECT id, senha FROM usuarios";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $senhaPura = $row['senha']; // Senha sem hash
    $senhaHash = password_hash($senhaPura, PASSWORD_DEFAULT);

    // Atualiza com o hash
    $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
    $stmt->bind_param("si", $senhaHash, $id);
    $stmt->execute();
}

echo "Senhas atualizadas com hash!";
?>
