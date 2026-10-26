<?php
/*
 *  Última versão do NekyLauncher no GitHub Releases.
 *  A consulta é feita pelo servidor e guardada em cache/ (o visitante nunca fala com a API),
 *  então a página funciona sem JavaScript e não esbarra no limite de 60 consultas/hora.
 *  Se o GitHub estiver fora, usa a última resposta guardada; se não houver nenhuma,
 *  a página mostra só o link para a página de Releases.
 */

/** Os quatro arquivos que o build publica, reconhecidos pelo nome. */
const NL_ARQUIVOS = [
    'windows-installer' => '/\.exe$/i',
    'windows-portable'  => '/windows-x64\.zip$/i',
    'linux-deb'         => '/\.deb$/i',
    'linux-portable'    => '/linux-x64\.tar\.gz$/i',
];

function nl_cache_dir(): string {
    $d = __DIR__ . '/../cache';
    if (is_dir($d) && is_writable($d)) return $d;
    $t = rtrim(sys_get_temp_dir(), '/\\') . '/nekylauncher-lp';
    if (!is_dir($t)) @mkdir($t, 0700, true);
    return $t;
}

/** GET com timeout curto. Devolve [status, corpo, cabeçalhos]. */
function nl_http_get(string $url, array $headers): array {
    if (function_exists('curl_init')) {
        $resp = [];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$resp) {
                $p = explode(':', $line, 2);
                if (count($p) === 2) $resp[strtolower(trim($p[0]))] = trim($p[1]);
                return strlen($line);
            },
        ]);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return [$body === false ? 0 : $code, $body === false ? '' : $body, $resp];
    }
    $ctx = stream_context_create(['http' => [
        'method' => 'GET', 'header' => implode("\r\n", $headers), 'timeout' => 5, 'ignore_errors' => true,
    ]]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0; $resp = [];
    foreach ($http_response_header ?? [] as $h) {
        if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) $code = (int)$m[1];
        else { $p = explode(':', $h, 2); if (count($p) === 2) $resp[strtolower(trim($p[0]))] = trim($p[1]); }
    }
    return [$code, $body === false ? '' : $body, $resp];
}

/**
 * Release mais recente, já organizada:
 * ['versao','tag','titulo','data','notas','pagina','arquivos' => [tipo => ['nome','url','tamanho']]]
 * ou null se nunca foi possível consultar.
 */
function nl_ultima_versao(array $cfg): ?array {
    $arq = nl_cache_dir() . '/release.json';
    $cache = is_file($arq) ? json_decode((string)file_get_contents($arq), true) : null;
    $ttl = (int)($cfg['cache_ttl'] ?? 600);

    if ($cache && isset($cache['quando']) && time() - $cache['quando'] < $ttl) {   // (release null = falha recente)
        return $cache['release'];
    }

    // permite apontar para um servidor de teste: NL_GITHUB_API=http://127.0.0.1:9000
    $api = rtrim(getenv('NL_GITHUB_API') ?: 'https://api.github.com', '/');
    $headers = [
        'Accept: application/vnd.github+json',
        'X-GitHub-Api-Version: 2022-11-28',
        'User-Agent: nekylauncher-lp (+https://launcher.nekyoh.tech)',
    ];
    $token = getenv('GITHUB_TOKEN') ?: ($cfg['github_token'] ?? '');
    if ($token !== '') $headers[] = 'Authorization: Bearer ' . $token;
    if (!empty($cache['etag'])) $headers[] = 'If-None-Match: ' . $cache['etag'];

    [$code, $body, $resp] = nl_http_get($api . '/repos/' . $cfg['repo'] . '/releases/latest', $headers);

    if ($code === 304 && !empty($cache['release'])) {                      // nada mudou (e não gasta a cota)
        $cache['quando'] = time();
        @file_put_contents($arq, json_encode($cache), LOCK_EX);
        return $cache['release'];
    }
    if ($code === 200 && ($j = json_decode($body, true)) && isset($j['tag_name'])) {
        $release = nl_organizar($j);
        @file_put_contents($arq, json_encode(['quando' => time(), 'etag' => $resp['etag'] ?? '', 'release' => $release]), LOCK_EX);
        return $release;
    }
    // falhou: usa a última resposta boa (mesmo velha) e tenta de novo daqui a 2 minutos
    // (sem nenhuma resposta guardada, lembra da falha também: assim um GitHub fora do ar
    //  não deixa cada visita esperando o tempo limite)
    $cache = $cache ?: ['etag' => '', 'release' => null];
    $cache['quando'] = time() - $ttl + 120;
    @file_put_contents($arq, json_encode($cache), LOCK_EX);
    return $cache['release'];
}

function nl_organizar(array $j): array {
    $arquivos = [];
    foreach ($j['assets'] ?? [] as $a) {
        foreach (NL_ARQUIVOS as $tipo => $re) {
            if (!isset($arquivos[$tipo]) && preg_match($re, $a['name'] ?? '')) {
                $arquivos[$tipo] = [
                    'nome'    => $a['name'],
                    'url'     => $a['browser_download_url'],
                    'tamanho' => (int)($a['size'] ?? 0),
                ];
            }
        }
    }
    $tag = (string)$j['tag_name'];
    return [
        'versao'   => ltrim($tag, 'vV'),
        'tag'      => $tag,
        'titulo'   => (string)($j['name'] ?: $tag),
        'data'     => (string)($j['published_at'] ?? ''),
        'notas'    => (string)($j['body'] ?? ''),
        'pagina'   => (string)($j['html_url'] ?? ''),
        'arquivos' => $arquivos,
    ];
}

/* ---------------- Markdown das notas da versão ----------------
 * Só o que as notas usam: títulos, listas, negrito, `código`, links e URLs soltas.
 * Tudo é escapado antes; só http(s) vira link.
 */
function nl_md_inline(string $s): string {
    $s = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $s = preg_replace('/`([^`]+)`/', '<code>$1</code>', $s);
    $s = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $s);
    $s = preg_replace('/(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])/', '<em>$1</em>', $s);
    $s = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/', fn($m) =>
        '<a href="' . $m[2] . '" target="_blank" rel="noopener">' . $m[1] . '</a>', $s);
    // URLs soltas (fora de href já criado)
    $s = preg_replace_callback('/(?<!href=")(?<!">)(https?:\/\/[^\s<]+[^\s<.,;:)])/', function ($m) {
        $texto = preg_replace('#^https://github\.com/[^/]+/[^/]+/compare/#', '', $m[1]);
        return '<a href="' . $m[1] . '" target="_blank" rel="noopener">' . $texto . '</a>';
    }, $s);
    // @usuario do GitHub
    $s = preg_replace('/(^|\s)@([A-Za-z0-9-]{1,39})\b/', '$1<a href="https://github.com/$2" target="_blank" rel="noopener">@$2</a>', $s);
    return $s;
}

function nl_markdown(string $md): string {
    $md = str_replace("\r", '', trim($md));
    if ($md === '') return '';
    $out = []; $lista = false; $par = [];
    $fechaPar = function () use (&$par, &$out) {
        if ($par) { $out[] = '<p>' . implode('<br>', array_map('nl_md_inline', $par)) . '</p>'; $par = []; }
    };
    foreach (explode("\n", $md) as $linha) {
        $l = rtrim($linha);
        if (preg_match('/^\s*[-*+]\s+(.*)$/', $l, $m)) {
            $fechaPar();
            if (!$lista) { $out[] = '<ul>'; $lista = true; }
            $out[] = '<li>' . nl_md_inline($m[1]) . '</li>';
            continue;
        }
        if ($lista) { $out[] = '</ul>'; $lista = false; }
        if (preg_match('/^(#{1,6})\s+(.*)$/', $l, $m)) {
            $fechaPar();
            $n = min(4, strlen($m[1]) + 2);                 // ## vira <h4>, # vira <h3>
            $out[] = "<h$n>" . nl_md_inline($m[2]) . "</h$n>";
        } elseif ($l === '' || preg_match('/^(-{3,}|\*{3,})$/', $l)) {
            $fechaPar();
        } else {
            $par[] = $l;
        }
    }
    $fechaPar();
    if ($lista) $out[] = '</ul>';
    return implode("\n", $out);
}
