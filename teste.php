<?php
declare(strict_types=1);

require_once __DIR__ . '/src/Cardapio.php';
require_once __DIR__ . '/src/Prato.php';
require_once __DIR__ . '/src/Bebida.php';
require_once __DIR__ . '/src/Sobremesa.php';
require_once __DIR__ . '/src/ItemComanda.php';
require_once __DIR__ . '/src/Comanda.php';

function dinheiro(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function titulo(string $texto): void
{
    echo "\n=== {$texto} ===\n";
}

/** Executa $acao e confirma que uma Exception foi lançada (a regra recusou). */
function esperarRecusa(string $descricao, callable $acao): void
{
    try {
        $acao();
        echo "ERRO: {$descricao} foi aceito\n";
    } catch (Exception $e) {
        echo "OK, recusado ({$descricao}): {$e->getMessage()}\n";
    }
}

/** Compara o preço calculado com o esperado (conta feita à mão). */
function conferirValor(string $descricao, float $esperado, float $obtido): void
{
    if (abs($esperado - $obtido) < 0.001) {
        echo "OK, {$descricao}: " . dinheiro($obtido) . "\n";
    } else {
        echo "ERRO, {$descricao}: esperado " . dinheiro($esperado) . ', obtido ' . dinheiro($obtido) . "\n";
    }
}

titulo('1. Cardápio e polimorfismo (mesmo método, respostas diferentes)');
$cardapio = new Cardapio('Sabor do Sertão');
$cardapio->adicionarItem(new Prato('Baião de dois', 28.00));
$cardapio->adicionarItem(new Prato('Carne de sol executivo', 35.00, true));
$cardapio->adicionarItem(new Bebida('Suco de caju', 8.00, true));
$cardapio->adicionarItem(new Bebida('Água de coco', 6.00, false));
$cardapio->adicionarItem(new Sobremesa('Doce de leite', 9.00));

foreach ($cardapio->getItens() as $item) {
    // Cada classe filha calcula o preço do seu jeito.
    echo "{$item->getTipo()}: {$item->getNome()} | 3 unidades: "
        . dinheiro($item->calcularPreco(3)) . "\n";
}

titulo('2. Comanda: composição (adicionar itens e calcular o total)');
$comanda = new Comanda(5);
$comanda->adicionarItem(new ItemComanda($cardapio->buscarPorNumero(2), 2)); // 2 x 35 + 2 x 4 = 78
$comanda->adicionarItem(new ItemComanda($cardapio->buscarPorNumero(3), 2)); // 2 x 8 + 2 x 1 = 18
$comanda->adicionarItem(new ItemComanda($cardapio->buscarPorNumero(5), 3)); // 3 x 9 com 10% = 24,30

foreach ($comanda->getItens() as $linha) {
    echo "{$linha->getQuantidade()} x {$linha->getItem()->getNome()}: "
        . dinheiro($linha->calcularSubtotal()) . "\n";
}
echo 'Total da mesa ' . $comanda->getNumeroMesa() . ': ' . dinheiro($comanda->calcularTotal()) . "\n";

titulo('3. Regras de negócio recusadas (throw new Exception + try/catch)');
esperarRecusa('prato com preço zero', fn() => new Prato('Prato grátis', 0));
esperarRecusa('item com nome vazio', fn() => new Bebida('   ', 5.00));
esperarRecusa('alterar o preço para valor negativo', fn() => $cardapio->getItens()[0]->setPreco(-10));
esperarRecusa('item de cardápio repetido', fn() => $cardapio->adicionarItem(new Prato('baião de dois', 30.00)));
esperarRecusa('quantidade zero na comanda', fn() => new ItemComanda($cardapio->buscarPorNumero(1), 0));
esperarRecusa('cálculo de preço com quantidade negativa', fn() => $cardapio->buscarPorNumero(1)->calcularPreco(-1));
esperarRecusa('mesa número zero', fn() => new Comanda(0));
esperarRecusa('fechar comanda vazia', fn() => (new Comanda(7))->fechar());


titulo('4. Fechar a comanda e tentar usar depois');
$comanda->fechar();
echo 'Comanda fechada? ' . ($comanda->isFechada() ? 'sim' : 'não') . "\n";
esperarRecusa('adicionar item em comanda fechada', fn() => $comanda->adicionarItem(new ItemComanda($cardapio->buscarPorNumero(1), 1)));
esperarRecusa('fechar a comanda duas vezes', fn() => $comanda->fechar());

titulo('5. paraArray() e json_encode (o que a rota ?formato=json vai devolver)');
echo json_encode($comanda->paraArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
echo "\nBusca de item inexistente: ";
echo $cardapio->buscarPorNumero(99) === null ? "null (a página vai responder 404)\n" : "ERRO: encontrou um item\n";

titulo('6. Regras de preço conferidas com o valor esperado');
$simples = new Prato('Baião de dois', 28.00);
$executivo = new Prato('Carne de sol executivo', 35.00, true);
conferirValor('prato simples, 3 unidades (3 x 28,00)', 84.00, $simples->calcularPreco(3));
conferirValor('prato executivo, 1 unidade (35,00 + 4,00)', 39.00, $executivo->calcularPreco(1));
conferirValor('prato executivo, 3 unidades (3 x 35,00 + 3 x 4,00)', 117.00, $executivo->calcularPreco(3));

$gelada = new Bebida('Suco de caju', 8.00, true);
$natural = new Bebida('Água de coco', 6.00, false);
conferirValor('bebida gelada, 1 unidade (8,00 + 1,00)', 9.00, $gelada->calcularPreco(1));
conferirValor('bebida gelada, 3 unidades (3 x 8,00 + 3 x 1,00)', 27.00, $gelada->calcularPreco(3));
conferirValor('bebida natural, 3 unidades (3 x 6,00, sem taxa)', 18.00, $natural->calcularPreco(3));

$doce = new Sobremesa('Doce de leite', 9.00);
conferirValor('sobremesa, 2 unidades (sem desconto)', 18.00, $doce->calcularPreco(2));
conferirValor('sobremesa, 3 unidades (10% de desconto)', 24.30, $doce->calcularPreco(3));
conferirValor('sobremesa, 5 unidades (10% de desconto)', 40.50, $doce->calcularPreco(5));