<?php
declare(strict_types=1);

require_once __DIR__ . '/ItemComanda.php';

/** Comanda de uma mesa: guarda uma lista de ItemComanda */
class Comanda
{
    private int $numeroMesa;
    private bool $fechada = false;

    /** @var ItemComanda[] */
    private array $itens = [];

    public function __construct(int $numeroMesa)
    {
        if ($numeroMesa < 1) {
            throw new Exception('O número da mesa deve ser pelo menos 1.');
        }
        $this->numeroMesa = $numeroMesa;
    }

    public function getNumeroMesa(): int
    {
        return $this->numeroMesa;
    }

    public function isFechada(): bool
    {
        return $this->fechada;
    }

    /** @return ItemComanda[] */
    public function getItens(): array
    {
        return $this->itens;
    }

    public function adicionarItem(ItemComanda $item): void
    {
        if ($this->fechada) {
            throw new Exception('A comanda está fechada e não aceita novos itens.');
        }
        $this->itens[] = $item;
    }

    public function calcularTotal(): float
    {
        $total = 0.0;
        foreach ($this->itens as $item) {
            $total += $item->calcularSubtotal();
        }
        return round($total, 2);
    }

    public function fechar(): void
    {
        if ($this->fechada) {
            throw new Exception('A comanda já está fechada.');
        }
        if (count($this->itens) === 0) {
            throw new Exception('Não é possível fechar uma comanda vazia.');
        }
        $this->fechada = true;
    }

    public function paraArray(): array
    {
        return [
            'mesa' => $this->numeroMesa,
            'fechada' => $this->fechada,
            'itens' => array_map(fn(ItemComanda $i) => $i->paraArray(), $this->itens),
            'total' => $this->calcularTotal(),
        ];
    }
}
