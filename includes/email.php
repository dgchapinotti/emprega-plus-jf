<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

function enviarEmailRecuperacao(string $destinatario, string $nome, string $link): void
{
    $configuracao = require __DIR__ . '/credenciais_email.php';

    if (($configuracao['senha'] ?? '') === 'COLE_AQUI_A_SENHA_DE_APLICATIVO') {
        throw new RuntimeException('As credenciais de e-mail ainda não foram configuradas.');
    }

    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $linkSeguro = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

    $email = new PHPMailer(true);

    try {
        $email->isSMTP();
        $email->Host = $configuracao['host'];
        $email->SMTPAuth = true;
        $email->Username = $configuracao['usuario'];
        $email->Password = $configuracao['senha'];
        $email->SMTPSecure = $configuracao['criptografia'];
        $email->Port = (int) $configuracao['porta'];
        $email->CharSet = 'UTF-8';
        $email->Timeout = 20;

        $email->setFrom($configuracao['remetente_email'], $configuracao['remetente_nome']);
        $email->addAddress($destinatario, $nome);
        $email->isHTML(true);
        $email->Subject = 'Redefinição de senha — Emprega+ Juiz de Fora';
        $email->Body = <<<HTML
            <h2>Redefinição de senha</h2>
            <p>Olá, {$nomeSeguro}.</p>
            <p>Recebemos uma solicitação para redefinir a senha da sua conta no Emprega+ Juiz de Fora.</p>
            <p><a href="{$linkSeguro}" style="background:#1351b4;color:#fff;padding:12px 20px;text-decoration:none;border-radius:6px;display:inline-block;">Definir nova senha</a></p>
            <p>Este link é válido por 30 minutos e pode ser usado apenas uma vez.</p>
            <p>Se você não solicitou a alteração, ignore esta mensagem. Sua senha atual continuará válida.</p>
        HTML;
        $email->AltBody = "Olá, {$nome}. Redefina sua senha pelo link: {$link}. O link é válido por 30 minutos e pode ser usado apenas uma vez.";
        $email->send();
    } catch (Exception $erro) {
        throw new RuntimeException('Falha no envio do e-mail: ' . $email->ErrorInfo, 0, $erro);
    }
}

