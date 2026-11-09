<?php
declare(strict_types=1);

// 1. LÓGICA (W5): tudo em PHP aqui no topo; o HTML só aparece depois.
require_once __DIR__ . '/../src/Cardapio.php';
require_once __DIR__ . '/../src/Prato.php';
require_once __DIR__ . '/../src/Bebida.php';
require_once __DIR__ . '/../src/Sobremesa.php';
require_once __DIR__ . '/../src/ItemComanda.php';
require_once __DIR__ . '/../src/Comanda.php';

/** W4: escapa qualquer dado antes de colocá-lo no HTML. */
function e(string|int|float $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function dinheiro(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/** Lê um valor de $_GET ou $_POST como texto. Se não for texto (ex.: ?pagina[]=x), devolve ''. */
function texto(array $origem, string $chave, string $padrao = ''): string
{
    $valor = $origem[$chave] ?? $padrao;
    return is_string($valor) ? $valor : '';
}

function detalhe(ItemCardapio $item): string
{
    if ($item instanceof Prato) {
        return $item->isExecutivo() ? 'Executivo (com acompanhamento)' : 'Prato simples';
    }
    if ($item instanceof Bebida) {
        return $item->isGelada() ? 'Gelada' : 'Natural';
    }
    if ($item instanceof Sobremesa) {
        return $item->isDiet() ? 'Diet' : 'Tradicional';
    }
    return '';
}

function montarCardapio(): Cardapio
{
    $cardapio = new Cardapio('Sabor do Sertão');
    $cardapio->adicionarItem(new Prato('Baião de dois', 28.00));
    $cardapio->adicionarItem(new Prato('Carne de sol executivo', 35.00, true));
    $cardapio->adicionarItem(new Bebida('Suco de caju', 8.00, true));
    $cardapio->adicionarItem(new Bebida('Água de coco', 6.00, false));
    $cardapio->adicionarItem(new Sobremesa('Doce de leite', 9.00));
    $cardapio->adicionarItem(new Sobremesa('Pudim diet', 11.00, true));
    return $cardapio;
}

function montarComanda(Cardapio $cardapio): Comanda
{
    $comanda = new Comanda(5);
    $comanda->adicionarItem(new ItemComanda($cardapio->buscarPorNumero(2), 2));
    $comanda->adicionarItem(new ItemComanda($cardapio->buscarPorNumero(3), 2));
    $comanda->adicionarItem(new ItemComanda($cardapio->buscarPorNumero(5), 3));
    return $comanda;
}

$cardapio = montarCardapio();
$comanda = montarComanda($cardapio);

// Páginas do menu (W1). A página "item" não aparece no menu, mas existe.
$menu = [
    'inicio' => 'Início',
    'cardapio' => 'Cardápio',
    'comanda' => 'Comanda',
];

// --- W1: escolha da página pela query string ---
$formato = texto($_GET, 'formato');
$pagina = texto($_GET, 'pagina', $formato === 'json' ? 'cardapio' : 'inicio');

$status = 200;
$mensagem404 = '';
$item = null;
$numeroItem = false;

if ($pagina === 'item') {
    $numeroItem = filter_var(texto($_GET, 'numero'), FILTER_VALIDATE_INT);
    $item = $numeroItem === false ? null : $cardapio->buscarPorNumero($numeroItem);
    if ($item === null) {
        $status = 404;
        $mensagem404 = 'Item do cardápio não encontrado.';
    }
} elseif (!array_key_exists($pagina, $menu)) {
    $status = 404;
    $mensagem404 = 'Página não encontrada.';
}

// --- W2: rota JSON (?formato=json) ---
if ($formato === 'json') {
    header('Content-Type: application/json; charset=utf-8');

    if ($status === 200 && !in_array($pagina, ['cardapio', 'comanda', 'item'], true)) {
        $status = 404;
        $mensagem404 = 'Esta página não tem versão em JSON.';
    }
    if ($status !== 200) {
        http_response_code($status);
        echo json_encode(['erro' => $mensagem404], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $dados = match ($pagina) {
        'cardapio' => $cardapio->paraArray(),
        'comanda' => $comanda->paraArray(),
        'item' => $item->paraArray(),
    };
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// --- Página do item: calcula o preço para a quantidade pedida ---
$quantidade = 1;
$precoCalculado = null;
$erroQuantidade = null;

if ($pagina === 'item' && $item !== null) {
    $pedida = filter_var(texto($_GET, 'quantidade', '1'), FILTER_VALIDATE_INT);
    try {
        if ($pedida === false) {
            throw new Exception('Informe a quantidade como um número inteiro.');
        }
        $quantidade = $pedida;
        $precoCalculado = $item->calcularPreco($pedida);
    } catch (Exception $e) {
        $erroQuantidade = $e->getMessage();
        $status = 422;
    }
}

// --- Dados usados pelo HTML ---
$ativa = $pagina === 'item' ? 'cardapio' : $pagina;
if ($status === 404) {
    $titulo = 'Não encontrado';
} elseif ($pagina === 'item') {
    $titulo = $item?->getNome() ?? 'Item';
} else {
    $titulo = $menu[$pagina];
}

if ($status === 422) {
    // O PHP não conhece o texto do 422, então o escrevemos à mão.
    header('HTTP/1.1 422 Unprocessable Entity', true, 422);
} else {
    http_response_code($status);
}
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?> | <?= e($cardapio->getNome()) ?></title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <header class="topo">
    <div class="topo-conteudo">
      <h1><?= e($cardapio->getNome()) ?></h1>
      <nav>
        <?php foreach ($menu as $chave => $rotulo): ?>
          <a href="?pagina=<?= e($chave) ?>"<?= $chave === $ativa ? ' class="ativo"' : '' ?>><?= e($rotulo) ?></a>
        <?php endforeach; ?>
      </nav>
    </div>
  </header>

  <main>
<?php if ($status === 404): ?>
    <h2>404: <?= e($mensagem404) ?></h2>
    <p>O endereço que você abriu não existe neste sistema.</p>
    <p><a class="botao" href="?pagina=cardapio">Ver o cardápio</a></p>

<?php elseif ($pagina === 'inicio'): ?>
    <h2>Bem-vindo</h2>
    <p>
      Sistema web em PHP orientado a objetos para um restaurante: o cardápio tem pratos,
      bebidas e sobremesas, e cada mesa tem uma comanda com os itens pedidos.
    </p>
    <div class="cartoes">
      <div class="cartao">
        <strong><?= e(count($cardapio->getItens())) ?></strong>
        <span>itens no cardápio</span>
      </div>
      <div class="cartao">
        <strong><?= e(count($comanda->getItens())) ?></strong>
        <span>linhas na comanda da mesa <?= e($comanda->getNumeroMesa()) ?></span>
      </div>
    </div>
    <p>
      <a class="botao" href="?pagina=cardapio">Ver o cardápio</a>
      <a class="botao secundario" href="?pagina=cardapio&amp;formato=json">Cardápio em JSON</a>
    </p>

<?php elseif ($pagina === 'cardapio'): ?>
    <h2>Cardápio</h2>
    <p>O preço de 3 unidades mostra o polimorfismo: cada tipo de item calcula do seu jeito.</p>
    <div class="tabela">
      <table>
        <thead>
          <tr><th>Nº</th><th>Tipo</th><th>Item</th><th>Detalhe</th><th>Preço base</th><th>3 unidades</th></tr>
        </thead>
        <tbody>
          <?php foreach ($cardapio->getItens() as $i => $it): ?>
          <tr>
            <td><?= e($i + 1) ?></td>
            <td><?= e($it->getTipo()) ?></td>
            <td><a href="?pagina=item&amp;numero=<?= e($i + 1) ?>"><?= e($it->getNome()) ?></a></td>
            <td><?= e(detalhe($it)) ?></td>
            <td><?= e(dinheiro($it->getPreco())) ?></td>
            <td><?= e(dinheiro($it->calcularPreco(3))) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p><a class="botao secundario" href="?pagina=cardapio&amp;formato=json">Ver em JSON</a></p>

<?php elseif ($pagina === 'item' && $item !== null): ?>
    <h2><?= e($item->getNome()) ?></h2>
    <dl class="detalhes">
      <dt>Tipo</dt><dd><?= e($item->getTipo()) ?></dd>
      <dt>Detalhe</dt><dd><?= e(detalhe($item)) ?></dd>
      <dt>Preço base</dt><dd><?= e(dinheiro($item->getPreco())) ?></dd>
    </dl>

    <h3>Calcular preço</h3>
    <form method="get" action="" class="linha">
      <input type="hidden" name="pagina" value="item">
      <input type="hidden" name="numero" value="<?= e($numeroItem) ?>">
      <label>Quantidade
        <input type="text" name="quantidade" inputmode="numeric" value="<?= e($quantidade) ?>">
      </label>
      <button type="submit" class="botao">Calcular</button>
    </form>
    <?php if ($erroQuantidade !== null): ?>
      <p class="erro" role="alert"><?= e($erroQuantidade) ?></p>
    <?php elseif ($precoCalculado !== null): ?>
      <p class="ok"><?= e($quantidade) ?> unidade(s): <strong><?= e(dinheiro($precoCalculado)) ?></strong></p>
    <?php endif; ?>

    <p>
      <a class="botao secundario" href="?pagina=cardapio">Voltar ao cardápio</a>
      <a class="botao secundario" href="?pagina=item&amp;numero=<?= e($numeroItem) ?>&amp;formato=json">Ver em JSON</a>
    </p>

<?php elseif ($pagina === 'comanda'): ?>
    <h2>Comanda da mesa <?= e($comanda->getNumeroMesa()) ?></h2>
    <p>Situação: <span class="selo"><?= $comanda->isFechada() ? 'fechada' : 'aberta' ?></span></p>
    <div class="tabela">
      <table>
        <thead>
          <tr><th>Qtd</th><th>Item</th><th>Tipo</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
          <?php foreach ($comanda->getItens() as $linha): ?>
          <tr>
            <td><?= e($linha->getQuantidade()) ?></td>
            <td><?= e($linha->getItem()->getNome()) ?></td>
            <td><?= e($linha->getItem()->getTipo()) ?></td>
            <td><?= e(dinheiro($linha->calcularSubtotal())) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><th colspan="3">Total</th><th><?= e(dinheiro($comanda->calcularTotal())) ?></th></tr>
        </tfoot>
      </table>
    </div>
    <p><a class="botao secundario" href="?pagina=comanda&amp;formato=json">Ver em JSON</a></p>

<?php endif; ?>
  </main>

  <footer class="rodape">
    Trabalho individual de Programação Web I · IFCE Campus Boa Viagem
  </footer>
</body>
</html>