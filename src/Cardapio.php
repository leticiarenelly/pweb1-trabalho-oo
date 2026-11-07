<?php
declare(strict_types=1);

require_once __DIR__ . '/ItemCardapio.php';

/** Cardápio do restaurante: guarda uma lista de ItemCardapio (Prato, Bebida, Sobremesa). */
class Cardapio
{
    private string $nome;

    /** @var ItemCardapio[] */
    private array $itens = [];

    public function __construct(string $nome)
    {
        $nome = trim($nome);
        if ($nome === '') {
            throw new Exception('O nome do restaurante não pode ser vazio.');
        }
        $this->nome = $nome;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    /** @return ItemCardapio[] */
    public function getItens(): array
    {
        return $this->itens;
    }

    public function adicionarItem(ItemCardapio $novo): void
    {
        foreach ($this->itens as $existente) {
            if (strtolower($existente->getNome()) === strtolower($novo->getNome())) {
                throw new Exception("Já existe um item chamado \"{$novo->getNome()}\" no cardápio.");
            }
        }
        $this->itens[] = $novo;
    }

    /** Busca pelo número do item (começa em 1). Devolve null se não existir */
    public function buscarPorNumero(int $numero): ?ItemCardapio
    {
        return $this->itens[$numero - 1] ?? null;
    }

    public function paraArray(): array
    {
        return [
            'restaurante' => $this->nome,
            'itens' => array_map(fn(ItemCardapio $i) => $i->paraArray(), $this->itens),
        ];
    }
}
