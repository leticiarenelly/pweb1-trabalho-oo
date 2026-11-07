<?php
declare(strict_types=1);

require_once __DIR__ . '/ItemCardapio.php';

/** Bebida: se for servida gelada, cobra uma taxa por unidade. */
class Bebida extends ItemCardapio
{
    private const TAXA_GELO = 1.00;

    private bool $gelada;

    public function __construct(string $nome, float $preco, bool $gelada = true)
    {
        parent::__construct($nome, $preco);
        $this->gelada = $gelada;
    }

    public function isGelada(): bool
    {
        return $this->gelada;
    }

    public function getTipo(): string
    {
        return 'Bebida';
    }

    public function calcularPreco(int $quantidade): float
    {
        $total = parent::calcularPreco($quantidade);
        if ($this->gelada) {
            $total += self::TAXA_GELO * $quantidade;
        }
        return round($total, 2);
    }

    public function paraArray(): array
    {
        return array_merge(parent::paraArray(), [
            'gelada' => $this->gelada,
        ]);
    }
}
 