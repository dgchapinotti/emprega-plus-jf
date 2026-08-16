<?php

declare(strict_types=1);

const NOME_SISTEMA = 'Emprega+ Juiz de Fora';
const SUBTITULO_SISTEMA = 'Sistema Municipal de Banco de Currículos e Empregabilidade';

$hostAtual = $_SERVER['HTTP_HOST'] ?? '';
$ambienteLocal = PHP_SAPI === 'cli'
    || $hostAtual === 'localhost'
    || str_starts_with($hostAtual, 'localhost:')
    || $hostAtual === '127.0.0.1'
    || str_starts_with($hostAtual, '127.0.0.1:');

define('AMBIENTE', $ambienteLocal ? 'local' : 'producao');
define('BASE_URL', $ambienteLocal ? '/Emprega_JF' : '');
define('URL_SISTEMA', $ambienteLocal
    ? 'http://localhost/Emprega_JF'
    : 'https://empregaplusjf.ifree.page');

function url(string $caminho = ''): string
{
    return BASE_URL . '/' . ltrim($caminho, '/');
}
