<?php

require_once __DIR__ . '/funcoes.php';
iniciarSessao();

$tituloPagina = isset($tituloPagina) && $tituloPagina !== ''
    ? $tituloPagina . ' | ' . NOME_SISTEMA
    : NOME_SISTEMA;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars(SUBTITULO_SISTEMA, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
