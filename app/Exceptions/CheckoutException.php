<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exceção para erros de negócio no checkout (estoque insuficiente, cupom inválido, etc.).
 * A mensagem é exibida diretamente ao usuário — deve ser clara e amigável.
 */
class CheckoutException extends RuntimeException {}
