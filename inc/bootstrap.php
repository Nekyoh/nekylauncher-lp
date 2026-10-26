<?php
/*
 *  Núcleo da página: configuração, idioma, sistema do visitante e funções de texto.
 *  Mesmo esquema do nekyoh.tech: ?lang=xx → cookie → idioma do navegador → padrão.
 */
$CFG = require __DIR__ . '/../config.php';
require __DIR__ . '/github.php';

const NL_COOKIE = 'nekyoh_lang';

function nl_idioma_navegador(string $header, array $disp): ?string {
    $c = [];
    foreach (explode(',', $header) as $i => $parte) {
        $bits = explode(';', trim($parte));
        $tag = strtolower(trim($bits[0]));
        if ($tag === '' || $tag === '*') continue;
        $q = 1.0;
        foreach (array_slice($bits, 1) as $p) if (preg_match('/^\s*q\s*=\s*([0-9.]+)\s*$/', $p, $m)) $q = (float)$m[1];
        if ($q > 0) $c[] = [$q, -$i, substr($tag, 0, 2)];
    }
    rsort($c);
    foreach ($c as [, , $l]) if (isset($disp[$l])) return $l;
    return null;
}

function nl_detectar_idioma(array $disp, string $padrao): string {
    $ok = fn($l) => is_string($l) && isset($disp[$l]);
    if (isset($_GET['lang']) && $ok($_GET['lang'])) {
        setcookie(NL_COOKIE, $_GET['lang'], [
            'expires' => time() + 31536000, 'path' => '/', 'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        return $_GET['lang'];
    }
    if ($ok($_COOKIE[NL_COOKIE] ?? null)) return $_COOKIE[NL_COOKIE];
    return nl_idioma_navegador($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', $disp) ?? $padrao;
}

$LANG = nl_detectar_idioma($CFG['idiomas'], $CFG['idioma_padrao']);
$TXT = require __DIR__ . "/../lang/{$LANG}.php";
$TXT_PADRAO = $LANG === 'en' ? $TXT : require __DIR__ . '/../lang/en.php';

/** Texto traduzido; t('x', ['v' => '1.2']) troca {v}. */
function t(string $k, array $vars = []) {
    global $TXT, $TXT_PADRAO;
    $v = $TXT[$k] ?? $TXT_PADRAO[$k] ?? $k;
    if (is_string($v)) foreach ($vars as $a => $b) $v = str_replace('{' . $a . '}', (string)$b, $v);
    return $v;
}

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** Texto com **negrito** e `código` (para frases dos arquivos de idioma). */
function tx(string $k, array $vars = []): string {
    $s = e(t($k, $vars));
    $s = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $s);
    return preg_replace('/`([^`]+)`/', '<code>$1</code>', $s);
}

/** Link para esta página em outro idioma, mantendo os outros parâmetros. */
function link_idioma(string $l): string {
    $q = $_GET; $q['lang'] = $l;
    return '?' . http_build_query($q);
}

/** Sistema de quem visita: windows | linux | mac | mobile | outro (?os= força). */
function nl_sistema(): string {
    $f = $_GET['os'] ?? '';
    if (in_array($f, ['windows', 'linux'], true)) return $f;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/Android|iPhone|iPad|iPod|Mobile/i', $ua)) return 'mobile';
    if (stripos($ua, 'Windows') !== false) return 'windows';
    if (preg_match('/Macintosh|Mac OS X/i', $ua)) return 'mac';
    if (preg_match('/Linux|X11|CrOS|FreeBSD/i', $ua)) return 'linux';
    return 'outro';
}

function nl_tamanho(int $bytes): string {
    if ($bytes <= 0) return '';
    $mb = $bytes / 1048576;
    return number_format($mb, $mb < 10 ? 1 : 0, t('num.dec'), t('num.mil')) . ' MB';
}

function nl_data(string $iso): string {
    $ts = strtotime($iso);
    if (!$ts) return '';
    $meses = t('meses');
    return t('data.formato', ['d' => (int)date('j', $ts), 'm' => $meses[(int)date('n', $ts) - 1] ?? date('m', $ts), 'a' => date('Y', $ts)]);
}

// Cabeçalhos de segurança (funcionam mesmo sem mod_headers no Apache)
header("Content-Security-Policy: default-src 'self'; script-src 'none'; style-src 'self' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' data:; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Vary: Accept-Language, Cookie, User-Agent');
