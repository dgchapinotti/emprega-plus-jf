<?php

declare(strict_types=1);

function pdfTexto(string $texto): string
{
    $convertido = iconv('UTF-8', 'Windows-1252//TRANSLIT', $texto);
    $convertido = $convertido === false ? $texto : $convertido;
    return str_replace(['\\','(',')',"\r","\n"], ['\\\\','\\(','\\)',' ',' '], $convertido);
}

function pdfQuebrarLinha(string $texto, int $limite = 92): array
{
    $texto = trim(preg_replace('/\s+/', ' ', $texto) ?? $texto);
    if ($texto === '') return [''];
    return explode("\n", wordwrap($texto, $limite, "\n", true));
}

function gerarPdfSimples(string $titulo, string $periodo, array $secoes): string
{
    $paginas=[];$comandos=[];$y=790;$numeroPagina=1;
    $novaPagina = static function() use (&$comandos,&$y,&$numeroPagina,$titulo,$periodo): void {
        $comandos=[];$y=790;
        $comandos[]="0.05 0.28 0.58 rg 0 792 595 50 re f";
        $comandos[]="BT /F1 18 Tf 1 1 1 rg 48 818 Td (".pdfTexto('Emprega+ Juiz de Fora').") Tj ET";
        $comandos[]="BT /F1 12 Tf 0.08 0.12 0.20 rg 48 765 Td (".pdfTexto($titulo).") Tj ET";
        $comandos[]="BT /F1 9 Tf 0.35 0.38 0.44 rg 48 748 Td (".pdfTexto($periodo).") Tj ET";
        $comandos[]="BT /F1 8 Tf 0.35 0.38 0.44 rg 500 28 Td (Pagina {$numeroPagina}) Tj ET";
        $y=720;
    };
    $fecharPagina = static function() use (&$paginas,&$comandos,&$numeroPagina): void {$paginas[]=implode("\n",$comandos);$numeroPagina++;};
    $novaPagina();

    foreach($secoes as $secao){
        $cabecalho=(string)($secao['titulo']??'');$linhas=$secao['linhas']??[];
        if($y<100){$fecharPagina();$novaPagina();}
        $comandos[]="0.92 0.95 0.98 rg 44 ".($y-7)." 507 24 re f";
        $comandos[]="BT /F1 11 Tf 0.05 0.28 0.58 rg 52 {$y} Td (".pdfTexto($cabecalho).") Tj ET";$y-=32;
        foreach($linhas as $linha){
            foreach(pdfQuebrarLinha((string)$linha) as $parte){
                if($y<55){$fecharPagina();$novaPagina();}
                $comandos[]="BT /F1 9 Tf 0.12 0.14 0.18 rg 52 {$y} Td (".pdfTexto($parte).") Tj ET";$y-=14;
            }
            $y-=3;
        }
        $y-=10;
    }
    $fecharPagina();

    $objetos=[];
    $objetos[1]='<< /Type /Catalog /Pages 2 0 R >>';
    $objetos[3]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
    $paginaRefs=[];$obj=4;
    foreach($paginas as $conteudo){$paginaObj=$obj++;$conteudoObj=$obj++;$paginaRefs[]="{$paginaObj} 0 R";$objetos[$paginaObj]="<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents {$conteudoObj} 0 R >>";$objetos[$conteudoObj]="<< /Length ".strlen($conteudo)." >>\nstream\n{$conteudo}\nendstream";}
    $objetos[2]='<< /Type /Pages /Kids ['.implode(' ',$paginaRefs).'] /Count '.count($paginaRefs).' >>';
    ksort($objetos);$pdf="%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";$offsets=[0];
    foreach($objetos as $numero=>$conteudo){$offsets[$numero]=strlen($pdf);$pdf.="{$numero} 0 obj\n{$conteudo}\nendobj\n";}
    $xref=strlen($pdf);$max=max(array_keys($objetos));$pdf.="xref\n0 ".($max+1)."\n0000000000 65535 f \n";
    for($i=1;$i<=$max;$i++)$pdf.=sprintf("%010d 00000 n \n",$offsets[$i]??0);
    $pdf.="trailer\n<< /Size ".($max+1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    return $pdf;
}
