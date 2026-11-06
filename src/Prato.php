<?php
declare(strict_types=1);

require_once __DIR__ . '/ItemCardapio.php';

/** Prato: se for executivo, cobra uma taxa de acompanhamento por und. (UMA DAS RN) */
class Prato extends ItemCardapio
{
    private const TAXA_ACOMPANHAMENTO = 4.00;

    private bool $executivo;

    public function __construct(string $nome, float $preco, bool $executivo = false)
    {
        parent::__construct($nome, $preco);
        $this->executivo = $executivo;
    }

    public function isExecutivo(): bool
    {
        return $this->executivo;
    }

    public function getTipo(): string
    {
        return 'Prato';
    }

    public function calcularPreco(int $quantidade): float
    {
        $total = parent::calcularPreco($quantidade);
        if ($this->executivo) {
            $total += self::TAXA_ACOMPANHAMENTO * $quantidade;
        }
        return round($total, 2);
    }

    public function paraArray(): array
    {
        return array_merge(parent::paraArray(), [
            'executivo' => $this->executivo,
        ]);
    }
}