<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/conexao.php';

const ENDPOINT_IBGE = 'https://servicodados.ibge.gov.br/api/v1/localidades/regioes-intermediarias/3106/municipios';
const TOTAL_ESPERADO = 146;

if (!function_exists('curl_init')) {
    fwrite(STDERR, "A extensão cURL do PHP não está habilitada.\n");
    exit(1);
}

$curl = curl_init(ENDPOINT_IBGE);
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 45,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
    CURLOPT_USERAGENT => 'EmpregaJF/1.0',
]);

$resposta = curl_exec($curl);
$statusHttp = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
$erroCurl = curl_error($curl);
curl_close($curl);

if (!is_string($resposta) || $statusHttp !== 200) {
    fwrite(STDERR, "Não foi possível consultar o IBGE. HTTP {$statusHttp}. {$erroCurl}\n");
    exit(1);
}

$municipios = json_decode($resposta, true, 512, JSON_THROW_ON_ERROR);

if (!is_array($municipios) || count($municipios) !== TOTAL_ESPERADO) {
    $totalRecebido = is_array($municipios) ? count($municipios) : 0;
    fwrite(STDERR, "A consulta retornou {$totalRecebido} municípios; eram esperados " . TOTAL_ESPERADO . ".\n");
    exit(1);
}

usort($municipios, static fn (array $a, array $b): int => strcasecmp($a['nome'], $b['nome']));

$pdo->beginTransaction();

try {
    $inserir = $pdo->prepare(
        "INSERT INTO cidades (nome, uf, codigo_ibge)
         VALUES (?, 'MG', ?)
         ON DUPLICATE KEY UPDATE
            nome = VALUES(nome),
            uf = VALUES(uf),
            codigo_ibge = VALUES(codigo_ibge)"
    );

    foreach ($municipios as $municipio) {
        $inserir->execute([(string) $municipio['nome'], (string) $municipio['id']]);
    }

    $pdo->commit();
} catch (Throwable $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Erro ao gravar as cidades: {$erro->getMessage()}\n");
    exit(1);
}

$valoresSql = [];
foreach ($municipios as $municipio) {
    $nome = str_replace("'", "''", (string) $municipio['nome']);
    $codigo = preg_replace('/\D/', '', (string) $municipio['id']);
    $valoresSql[] = "('{$nome}', 'MG', '{$codigo}')";
}

$sql = "-- Municípios da Região Geográfica Intermediária de Juiz de Fora (IBGE 3106)\n"
    . "-- Fonte: " . ENDPOINT_IBGE . "\n\n"
    . "INSERT INTO cidades (nome, uf, codigo_ibge) VALUES\n"
    . implode(",\n", $valoresSql)
    . "\nON DUPLICATE KEY UPDATE\n"
    . "    nome = VALUES(nome),\n"
    . "    uf = VALUES(uf),\n"
    . "    codigo_ibge = VALUES(codigo_ibge);\n";

$arquivoSql = __DIR__ . '/cidades_regiao_juiz_de_fora.sql';
if (file_put_contents($arquivoSql, $sql) === false) {
    fwrite(STDERR, "As cidades foram importadas, mas não foi possível gerar o arquivo SQL.\n");
    exit(1);
}

fwrite(STDOUT, TOTAL_ESPERADO . " cidades importadas com sucesso.\n");
fwrite(STDOUT, "SQL gerado em: {$arquivoSql}\n");
