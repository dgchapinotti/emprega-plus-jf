<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/../includes/pdf-simples.php';

$candidatoId = obterCandidatoId($pdo);

$consulta = $pdo->prepare(
    "SELECT c.nome_completo, c.telefone, u.email, cid.nome AS cidade, cid.uf,
            cu.titulo_profissional, cu.objetivo_profissional, cu.resumo_profissional
     FROM candidatos c
     INNER JOIN usuarios u ON u.id = c.usuario_id
     LEFT JOIN cidades cid ON cid.id = c.cidade_id
     LEFT JOIN curriculos cu ON cu.candidato_id = c.id
     WHERE c.id = ? LIMIT 1"
);
$consulta->execute([$candidatoId]);
$candidato = $consulta->fetch();

if (!$candidato) {
    definirFlash('erro', 'Não foi possível gerar seu currículo.');
    redirecionar('candidato/painel.php');
}

$buscar = static function (PDO $pdo, string $sql, int $id): array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchAll();
};

$formacoes = $buscar($pdo, "SELECT * FROM formacoes WHERE candidato_id=? ORDER BY cursando DESC, COALESCE(data_conclusao,'9999-12-31') DESC", $candidatoId);
$cursos = $buscar($pdo, 'SELECT * FROM cursos WHERE candidato_id=? ORDER BY ano_conclusao DESC, nome', $candidatoId);
$experiencias = $buscar($pdo, 'SELECT * FROM experiencias WHERE candidato_id=? ORDER BY emprego_atual DESC, data_inicio DESC', $candidatoId);

$rotulosNivel = [
    'fundamental_incompleto' => 'Ensino Fundamental incompleto',
    'fundamental_completo' => 'Ensino Fundamental completo',
    'medio_incompleto' => 'Ensino Médio incompleto',
    'medio_completo' => 'Ensino Médio completo',
    'tecnico' => 'Curso técnico',
    'superior_incompleto' => 'Ensino Superior incompleto',
    'superior_completo' => 'Ensino Superior completo',
    'pos_graduacao' => 'Pós-graduação',
    'mestrado' => 'Mestrado',
    'doutorado' => 'Doutorado',
];

$telefone = (string) $candidato['telefone'];
$digitosTelefone = somenteDigitos($telefone);
if (strlen($digitosTelefone) === 11) {
    $telefone = preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $digitosTelefone) ?: $telefone;
}

$contato = implode('  |  ', array_filter([
    trim((string) $candidato['cidade'] . ' - ' . (string) $candidato['uf'], ' -'),
    $telefone,
    (string) $candidato['email'],
]));

$secoes = [];
if (!empty($candidato['objetivo_profissional']) || !empty($candidato['resumo_profissional'])) {
    $linhasPerfil = [];
    if (!empty($candidato['objetivo_profissional'])) $linhasPerfil[] = 'Objetivo: ' . $candidato['objetivo_profissional'];
    if (!empty($candidato['resumo_profissional'])) $linhasPerfil[] = $candidato['resumo_profissional'];
    $secoes[] = ['titulo' => 'Perfil profissional', 'linhas' => $linhasPerfil];
}

$linhasExperiencias = [];
foreach ($experiencias as $experiencia) {
    $inicio = date('m/Y', strtotime($experiencia['data_inicio']));
    $fim = $experiencia['emprego_atual']
        ? 'Atual'
        : (!empty($experiencia['data_fim']) ? date('m/Y', strtotime($experiencia['data_fim'])) : 'Não informado');
    $linha = $experiencia['cargo'] . ' - ' . $experiencia['empresa'] . ' (' . $inicio . ' a ' . $fim . ')';
    if (!empty($experiencia['descricao'])) $linha .= '. ' . $experiencia['descricao'];
    $linhasExperiencias[] = $linha;
}
$secoes[] = ['titulo' => 'Experiência profissional', 'linhas' => $linhasExperiencias ?: ['Nenhuma experiência profissional informada.']];

$linhasFormacoes = [];
foreach ($formacoes as $formacao) {
    $curso = $formacao['curso'] ?: ($rotulosNivel[$formacao['nivel']] ?? $formacao['nivel']);
    $situacao = $formacao['cursando']
        ? 'Cursando'
        : (!empty($formacao['data_conclusao']) ? 'Concluído em ' . date('Y', strtotime($formacao['data_conclusao'])) : 'Conclusão não informada');
    $linhasFormacoes[] = $curso . ' - ' . $formacao['instituicao'] . '. ' . ($rotulosNivel[$formacao['nivel']] ?? $formacao['nivel']) . ' - ' . $situacao;
}
$secoes[] = ['titulo' => 'Formação acadêmica', 'linhas' => $linhasFormacoes ?: ['Nenhuma formação acadêmica informada.']];

$linhasCursos = [];
foreach ($cursos as $curso) {
    $linha = $curso['nome'];
    if (!empty($curso['instituicao'])) $linha .= ' - ' . $curso['instituicao'];
    if (!empty($curso['carga_horaria'])) $linha .= ' (' . (int) $curso['carga_horaria'] . ' horas)';
    if (!empty($curso['ano_conclusao'])) $linha .= ' - Conclusão: ' . (int) $curso['ano_conclusao'];
    $linhasCursos[] = $linha;
}
$secoes[] = ['titulo' => 'Cursos complementares', 'linhas' => $linhasCursos ?: ['Nenhum curso complementar informado.']];

$titulo = (string) ($candidato['titulo_profissional'] ?: 'Currículo profissional');
$pdf = gerarPdfSimples($candidato['nome_completo'], $titulo . '  |  ' . $contato, $secoes);

$nomeArquivo = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $candidato['nome_completo']);
$nomeArquivo = $nomeArquivo === false ? 'candidato' : $nomeArquivo;
$nomeArquivo = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $nomeArquivo) ?? 'candidato', '-'));
$nomeArquivo = 'curriculo-' . ($nomeArquivo !== '' ? $nomeArquivo : 'candidato') . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, max-age=0, must-revalidate');
header('X-Content-Type-Options: nosniff');
echo $pdf;
exit;
