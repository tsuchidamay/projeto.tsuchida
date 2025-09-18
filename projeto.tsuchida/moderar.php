<?php
include "conexao.php"; // conexão com MySQL + variáveis do Cloudinary

// Função para deletar imagem do Cloudinary
function deletarImagemCloudinary($public_id, $cloud_name, $api_key, $api_secret) {
    $timestamp = time();
    $string_to_sign = "public_id=$public_id&timestamp=$timestamp$api_secret";
    $signature = sha1($string_to_sign);

    $data = [
        'public_id' => $public_id,
        'timestamp' => $timestamp,
        'api_key' => $api_key,
        'signature' => $signature
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/destroy");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

/*
COMPARAÇÃO: No código de recados/pedidos
- Não há função de deletar arquivos
- Não existe upload de imagem
- Apenas se deleta o registro do banco
*/

// Excluir produto
if(isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $res = mysqli_query($conexao, "SELECT imagem_url FROM produtos WHERE id = $id");
    $dados = mysqli_fetch_assoc($res);

    if($dados && !empty($dados['imagem_url'])) {
        $url = $dados['imagem_url'];
        $parts = explode("/", $url);
        $filename = end($parts);
        $public_id = pathinfo($filename, PATHINFO_FILENAME);
        deletarImagemCloudinary($public_id, $cloud_name, $api_key, $api_secret);
    }

    mysqli_query($conexao, "DELETE FROM produtos WHERE id = $id") or die("Erro ao excluir: " . mysqli_error($conexao));
    header("Location: moderar.php"); //substituir se estiver diferente
    exit;
}

/*
COMPARAÇÃO:
- Código de recados/pedidos: deletar não manipula imagens, só remove o registro
- Aqui é necessário deletar a imagem no Cloudinary antes de excluir do banco
*/

// Editar produto
if(isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    $preco = floatval($_POST['preco']);

    $update_sql = "UPDATE produtos SET nome='$nome', descricao='$descricao', preco=$preco WHERE id=$id";
    mysqli_query($conexao, $update_sql) or die("Erro ao atualizar: " . mysqli_error($conexao));
    header("Location: moderar.php");
    exit;
}

/*
COMPARAÇÃO:
- Código de recados/pedidos: não há edição inline
- Aqui o sistema permite editar nome, descrição e preço, mas não imagem
*/


// Selecionar produtos para exibição
$editar_id = isset($_GET['editar']) ? intval($_GET['editar']) : 0;
$produtos = mysqli_query($conexao, "SELECT * FROM produtos ORDER BY id DESC");

/*
COMPARAÇÃO:
- Código de recados/pedidos: SELECT * FROM recados ORDER BY id DESC
- Aqui seleciona produtos com imagens e preço
*/
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8"/>
<title>Moderar Produtos</title>
<link rel="stylesheet" href="produto.css"/>
</head>
<body>
<div id="main">
    <div id="geral">
        <div id="header">
            <h1>Moderar Produtos</h1>
        </div>

        <div class="produtos-container">
            <?php while($res = mysqli_fetch_assoc($produtos)): ?>
                <div class="produto">
                    <p><strong>ID:</strong> <?= $res['id'] ?></p>
                    <p><strong>Nome:</strong> <?= htmlspecialchars($res['nome']) ?></p>
                    <p><strong>Preço:</strong> R$ <?= number_format($res['preco'], 2, ',', '.') ?></p>
                    <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($res['descricao'])) ?></p>
                    <p><img src="<?= htmlspecialchars($res['imagem_url']) ?>" alt="<?= htmlspecialchars($res['nome']) ?>"></p>

                    <!-- Link para excluir -->
                    <a href="moderar.php?excluir=<?= $res['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>

                    <!-- Formulário de edição inline -->
                    <?php if($editar_id == $res['id']): ?>
                        <form method="post" action="moderar.php">
                            <input type="hidden" name="id" value="<?= $res['id'] ?>">
                            <input type="text" name="nome" value="<?= htmlspecialchars($res['nome']) ?>" required><br>
                            <textarea name="descricao" required><?= htmlspecialchars($res['descricao']) ?></textarea><br>
                            <input type="number" step="0.01" name="preco" value="<?= $res['preco'] ?>" required><br>
                            <input type="submit" name="editar" value="Salvar">
                            <a href="moderar.php">Cancelar</a>
                        </form>
                    <?php else: ?>
                        <a href="moderar.php?editar=<?= $res['id'] ?>">Editar</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
</body>
</html>