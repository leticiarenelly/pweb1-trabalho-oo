<?php
declare(strict_types=1);

/**
 * Classe mãe.
 * Prato, Bebida e Sobremesa são os menino que herdam da mãe */
class ItemCardapio
{
    protected string $nome;
    protected float $preco;

    public function __construct(string $nome, float $preco)
    {
        $this->setNome($nome);
        $this->setPreco($preco);
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function getTipo(): string
    {
        return 'Item';
    }

    private function setNome(string $nome): void
    {
        $nome = trim($nome);
        if ($nome === '') {
            throw new Exception('O nome do item não pode ser vazio.');
        }
        if (strlen($nome) > 60) {
            throw new Exception('O nome do item pode ter no máximo 60 caracteres.');
        }
        $this->nome = $nome;
    }

    public function setPreco(float $preco): void
    {
        if ($preco <= 0) {
            throw new Exception('O preço deve ser maior que zero.');
        }
        $this->preco = $preco;
    }

    protected function validarQuantidade(int $quantidade): void
    {
        if ($quantidade < 1) {
            throw new Exception('A quantidade deve ser pelo menos 1.');
        }
    }

    /** Preço total para a quantidade pedida. As filhas sobrescrevem este método. */
    public function calcularPreco(int $quantidade): float
    {
        $this->validarQuantidade($quantidade);
        return round($this->preco * $quantidade, 2);
    }

    public function paraArray(): array
    {
        return [
            'tipo' => $this->getTipo(),
            'nome' => $this->nome,
            'preco' => $this->preco,
        ];
    }
}