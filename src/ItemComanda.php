<?php
declare(strict_types=1);

require_once __DIR__ . '/ItemCardapio.php';

/** Uma linha da comanda: um item do cardápio e a quantidade pedida. */
class ItemComanda
{
    private ItemCardapio $item;
    private int $quantidade;

    public function __construct(ItemCardapio $item, int $quantidade)
    {
        if ($quantidade < 1) {
            throw new Exception('A quantidade deve ser pelo menos 1.');
        }
        $this->item = $item;
        $this->quantidade = $quantidade;
    }

    public function getItem(): ItemCardapio
    {
        return $this->item;
    }

    public function getQuantidade(): int
    {
        return $this->quantidade;
    }

    /** Polimorfismo: cada tipo de item calcula o seu preço do seu jeito. */
    public function calcularSubtotal(): float
    {
        return $this->item->calcularPreco($this->quantidade);
    }

    public function paraArray(): array
    {
        return [
            'item' => $this->item->paraArray(),
            'quantidade' => $this->quantidade,
            'subtotal' => $this->calcularSubtotal(),
        ];
    }
}
