<?php
declare(strict_types=1);

require_once __DIR__ . '/ItemCardapio.php';

/** Sobremesa: 10% de desconto a partir de 3 unidades. */
class Sobremesa extends ItemCardapio
{
    private const QUANTIDADE_PARA_DESCONTO = 3;
    private const DESCONTO = 0.10;

    private bool $diet;

    public function __construct(string $nome, float $preco, bool $diet = false)
    {
        parent::__construct($nome, $preco);
        $this->diet = $diet;
    }

    public function isDiet(): bool
    {
        return $this->diet;
    }

    public function getTipo(): string
    {
        return 'Sobremesa';
    }

    public function calcularPreco(int $quantidade): float
    {
        $total = parent::calcularPreco($quantidade);
        if ($quantidade >= self::QUANTIDADE_PARA_DESCONTO) {
            $total -= $total * self::DESCONTO;
        }
        return round($total, 2);
    }

    public function paraArray(): array
    {
        return array_merge(parent::paraArray(), [
            'diet' => $this->diet,
        ]);
    }
}
