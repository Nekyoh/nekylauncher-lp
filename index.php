<?php
require __DIR__ . '/inc/bootstrap.php';

$rel  = nl_ultima_versao($CFG);              // null = GitHub inacessível e sem cache
$arqs = $rel['arquivos'] ?? [];
$os   = nl_sistema();
$L    = $CFG['links'];

// botão principal: o arquivo recomendado para o sistema de quem visita
$principal = ['windows' => 'windows-installer', 'linux' => 'linux-deb'][$os] ?? null;
$alvo      = $principal && isset($arqs[$principal]) ? $arqs[$principal] : null;

// ordem das colunas de download: a do sistema detectado primeiro
$colunas = $os === 'linux'
    ? ['linux' => ['linux-deb', 'linux-portable'], 'windows' => ['windows-installer', 'windows-portable']]
    : ['windows' => ['windows-installer', 'windows-portable'], 'linux' => ['linux-deb', 'linux-portable']];

$shot = fn(string $n) => "assets/img/shots/{$LANG}-{$n}.webp";

/** Ícones de linha (24×24, traço 2) no mesmo estilo dos do launcher. */
function ic(string $n): string {
    $p = [
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'grid'     => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'cube'     => '<path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="m3 7 9 5 9-5M12 12v10"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        'shirt'    => '<path d="M8 3 3 6l2 5 3-1v11h8V10l3 1 2-5-5-3a4 4 0 0 1-8 0z"/>',
        'coffee'   => '<path d="M4 9h13v5a6 6 0 0 1-6 6h-1a6 6 0 0 1-6-6z"/><path d="M17 11h1.5a2.5 2.5 0 0 1 0 5H17M8 2v3M12 2v3"/>',
        'upload'   => '<path d="M12 15V4M7 9l5-5 5 5"/><path d="M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/>',
        'refresh'  => '<path d="M20 11a8 8 0 0 0-14.6-4.5L3 9"/><path d="M3 4v5h5M4 13a8 8 0 0 0 14.6 4.5L21 15"/><path d="M21 20v-5h-5"/>',
        'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        'download' => '<path d="M12 4v11M7 10l5 5 5-5"/><path d="M4 19h16"/>',
        'warning'  => '<path d="M12 3 2 20h20z"/><path d="M12 10v4M12 17h.01"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'terminal' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="m7 9 3 3-3 3M13 15h4"/>',
        'windows'  => '<path d="M3 5.5 10.5 4.5v7H3zM12 4.3 21 3v8.5h-9zM3 12.5h7.5v7L3 18.5zM12 12.5h9V21l-9-1.3z" fill="currentColor" stroke="none"/>',
        'github'   => '<path d="M9 19c-4 1.5-4-2-6-2.5M15 21v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.3 4.3 0 0 0-.1-3.2s-1-.3-3.4 1.3a11.6 11.6 0 0 0-6 0C6.8 2.8 5.8 3.1 5.8 3.1a4.3 4.3 0 0 0-.1 3.2A4.6 4.6 0 0 0 4.4 9.5c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V21"/>',
        'youtube'  => '<path d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .5 12 31 31 0 0 0 1 16.8a3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8zM9.7 15.1V8.9l5.8 3.1z" fill="currentColor" stroke="none"/>',
    ][$n] ?? '';
    return '<svg class="ic" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $p . '</svg>';
}

$recursos = [
    ['user', 'ms'], ['grid', 'inst'], ['cube', 'loader'],
    ['search', 'mods'], ['shirt', 'skin'], ['coffee', 'java'],
    ['upload', 'drop'], ['refresh', 'update'], ['globe', 'lang'],
];
$duvidas = ['free', 'orig', 'warn', 'data', 'update', 'mac', 'bug'];
?>
<!DOCTYPE html>
<html lang="<?= e(t('html')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(t('meta.title')) ?></title>
    <meta name="description" content="<?= e(t('meta.desc')) ?>">
    <meta name="theme-color" content="#0b0d12">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e(t('meta.title')) ?>">
    <meta property="og:description" content="<?= e(t('meta.desc')) ?>">
    <meta property="og:image" content="<?= e($CFG['url']) ?>/assets/img/og.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/icon-32.png">
    <link rel="icon" type="image/png" sizes="64x64" href="assets/img/icon-64.png">
    <link rel="apple-touch-icon" href="assets/img/icon-180.png">
    <?php $escolhido = isset($_GET['lang'], $CFG['idiomas'][$_GET['lang']]) ? $_GET['lang'] : null; ?>
    <link rel="canonical" href="<?= e($CFG['url'] . '/' . ($escolhido ? '?lang=' . $escolhido : '')) ?>">
    <?php foreach ($CFG['idiomas'] as $cod => $_): ?>
    <link rel="alternate" hreflang="<?= e($cod) ?>" href="<?= e($CFG['url']) ?>/?lang=<?= e($cod) ?>">
    <?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= e($CFG['url']) ?>/">
    <meta property="og:url" content="<?= e($CFG['url'] . '/' . ($escolhido ? '?lang=' . $escolhido : '')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<a class="pular" href="#download"><?= e(t('skip')) ?></a>

<header class="topo">
    <a class="marca" href="./">
        <img src="assets/img/logo-256.png" alt="" width="40" height="40">
        <span class="marca-nome">NekyLauncher</span>
        <span class="marca-by">by Nekyoh</span>
    </a>
    <nav class="menu" aria-label="<?= e(t('nav.features')) ?>">
        <a href="#recursos"><?= e(t('nav.features')) ?></a>
        <?php if ($rel): ?><a href="#novidades"><?= e(t('nav.news')) ?></a><?php endif; ?>
        <a href="#duvidas"><?= e(t('nav.faq')) ?></a>
        <a class="menu-cta" href="#download"><?= ic('download') ?><span><?= e(t('nav.download')) ?></span></a>
    </nav>
    <details class="idioma">
        <summary aria-label="<?= e(t('nav.lang')) ?>"><?= ic('globe') ?><span><?= e(strtoupper($LANG)) ?></span></summary>
        <ul>
            <?php foreach ($CFG['idiomas'] as $cod => $nome): ?>
            <li><a href="<?= e(link_idioma($cod)) ?>" hreflang="<?= e($cod) ?>"<?= $cod === $LANG ? ' aria-current="true"' : '' ?>><b><?= e(strtoupper($cod)) ?></b> <?= e($nome) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </details>
</header>

<main>
    <!-- ============ HERO ============ -->
    <section class="hero">
        <div class="hero-texto">
            <p class="kicker"><span class="ponto" aria-hidden="true"></span><?= e($rel ? t('hero.kicker', ['v' => $rel['versao']]) : t('hero.kicker0')) ?></p>
            <h1 class="glitch">NekyLauncher</h1>
            <p class="tagline"><?= e(t('hero.tagline')) ?></p>
            <p class="sub"><?= e(t('hero.sub')) ?></p>

            <div class="hero-acoes">
                <?php if ($alvo): ?>
                <a class="btn btn-jogar" href="<?= e($alvo['url']) ?>">
                    <?= ic($os === 'windows' ? 'windows' : 'download') ?>
                    <span><?= e(t('hero.btn.' . $os)) ?></span>
                </a>
                <?php else: ?>
                <a class="btn btn-jogar" href="<?= e($rel ? '#download' : $L['releases']) ?>">
                    <?= ic('download') ?><span><?= e(t('hero.btn.any')) ?></span>
                </a>
                <?php endif; ?>
                <a class="btn btn-sec" href="<?= e($L['github']) ?>" target="_blank" rel="noopener"><?= ic('github') ?><span>GitHub</span></a>
            </div>
            <?php if ($alvo): ?>
            <p class="hero-meta"><?= e(t('hero.meta.' . $os, ['s' => nl_tamanho($alvo['tamanho'])])) ?> · <a href="#download"><?= e(t('hero.other')) ?> ↓</a></p>
            <?php elseif ($os === 'mac' || $os === 'mobile'): ?>
            <p class="hero-meta aviso-os"><?= e(t('hero.' . $os)) ?></p>
            <?php endif; ?>
        </div>

        <figure class="janela">
            <div class="janela-barra" aria-hidden="true"><i></i><i></i><i></i></div>
            <img src="<?= e($shot('home')) ?>" alt="<?= e(t('hero.shot')) ?>" width="1280" height="800" fetchpriority="high">
        </figure>
    </section>

    <!-- ============ RECURSOS ============ -->
    <section id="recursos" class="secao">
        <header class="secao-cab">
            <h2><?= e(t('feat.title')) ?></h2>
            <p><?= e(t('feat.sub')) ?></p>
        </header>
        <div class="recursos">
            <?php foreach ($recursos as [$icone, $id]): ?>
            <article class="recurso">
                <span class="recurso-ic"><?= ic($icone) ?></span>
                <h3><?= e(t("feat.$id.t")) ?></h3>
                <p><?= e(t("feat.$id.d")) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ TELAS ============ -->
    <section class="secao telas-secao" aria-labelledby="telas-titulo">
        <header class="secao-cab"><h2 id="telas-titulo"><?= e(t('shots.title')) ?></h2></header>
        <div class="telas">
            <?php foreach (['mods', 'skins', 'new'] as $n): ?>
            <figure class="tela">
                <img src="<?= e($shot($n)) ?>" alt="" width="1280" height="800" loading="lazy">
                <figcaption><?= e(t("shots.$n")) ?></figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ DOWNLOAD ============ -->
    <section id="download" class="secao download">
        <header class="secao-cab">
            <h2><?= e(t('dl.title')) ?></h2>
            <p><?= $rel ? tx('dl.lede', ['v' => $rel['versao'], 'd' => nl_data($rel['data'])]) : e(t('dl.lede0')) ?></p>
        </header>

        <?php if (!$rel): ?>
        <div class="caixa caixa-aviso">
            <p><?= e(t('dl.offline')) ?></p>
            <a class="btn btn-jogar" href="<?= e($L['releases']) ?>" target="_blank" rel="noopener"><?= ic('github') ?><span><?= e(t('dl.offline.btn')) ?></span></a>
        </div>
        <?php else: ?>
        <div class="sistemas">
            <?php foreach ($colunas as $sis => $tipos): ?>
            <div class="sistema<?= $sis === $os ? ' sistema-seu' : '' ?>">
                <div class="sistema-cab">
                    <span class="sistema-ic"><?= ic($sis === 'windows' ? 'windows' : 'terminal') ?></span>
                    <div><h3><?= e(t("dl.$sis")) ?></h3><p><?= e(t("dl.$sis.req")) ?></p></div>
                </div>
                <?php foreach ($tipos as $i => $tipo): $a = $arqs[$tipo] ?? null; ?>
                <div class="arquivo">
                    <div class="arquivo-info">
                        <h4><?= e(t("dl.$tipo.t")) ?><?php if ($i === 0): ?> <span class="selo"><?= e(t('dl.rec')) ?></span><?php endif; ?></h4>
                        <p><?= e(t("dl.$tipo.d")) ?></p>
                        <?php if ($a): ?><p class="arquivo-nome"><code><?= e($a['nome']) ?></code> · <?= e(nl_tamanho($a['tamanho'])) ?></p><?php endif; ?>
                    </div>
                    <?php if ($a): ?>
                    <a class="btn <?= $i === 0 ? 'btn-jogar' : 'btn-sec' ?> btn-sm" href="<?= e($a['url']) ?>"><?= ic('download') ?><span><?= e(t('dl.btn')) ?></span></a>
                    <?php else: ?>
                    <span class="arquivo-falta"><?= e(t('dl.missing')) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($a && $tipo === 'linux-deb'): ?>
                <div class="cmd"><span><?= e(t('dl.deb.cmd')) ?></span><code>sudo apt install ./<?= e($a['nome']) ?></code></div>
                <?php elseif ($a && $tipo === 'linux-portable'): ?>
                <div class="cmd"><span><?= e(t('dl.tar.cmd')) ?></span><code>./NekyLauncher/bin/NekyLauncher</code></div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="caixa caixa-aviso">
            <span class="caixa-ic"><?= ic('warning') ?></span>
            <div>
                <h3><?= e(t('dl.warn.t')) ?></h3>
                <p><?= tx('dl.warn.d') ?></p>
            </div>
        </div>
        <p class="dl-rodape">
            <span><?= tx('dl.need') ?></span>
            <span class="dl-links">
                <a href="<?= e($L['releases']) ?>" target="_blank" rel="noopener"><?= e(t('dl.all')) ?> ↗</a>
                <a href="<?= e($L['github']) ?>" target="_blank" rel="noopener"><?= e(t('dl.source')) ?> ↗</a>
            </span>
        </p>
    </section>

    <!-- ============ NOVIDADES ============ -->
    <?php if ($rel): $notas = nl_markdown($rel['notas']); ?>
    <section id="novidades" class="secao">
        <header class="secao-cab">
            <h2><?= e(t('news.title', ['v' => $rel['versao']])) ?></h2>
            <p><?= e(nl_data($rel['data'])) ?></p>
        </header>
        <article class="notas">
            <?= $notas !== '' ? $notas : '<p>' . e(t('news.empty')) . '</p>' ?>
            <p class="notas-mais"><a href="<?= e($L['releases']) ?>" target="_blank" rel="noopener"><?= e(t('news.all')) ?> ↗</a></p>
        </article>
    </section>
    <?php endif; ?>

    <!-- ============ DÚVIDAS ============ -->
    <section id="duvidas" class="secao">
        <header class="secao-cab"><h2><?= e(t('faq.title')) ?></h2></header>
        <div class="duvidas">
            <?php foreach ($duvidas as $d): ?>
            <details>
                <summary><?= e(t("faq.$d.q")) ?></summary>
                <p><?= e(t("faq.$d.a")) ?><?php if ($d === 'bug'): ?> <a href="<?= e($L['issues']) ?>" target="_blank" rel="noopener"><?= e(t('faq.bug.link')) ?> ↗</a><?php endif; ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ MAIS DA NEKYOH ============ -->
    <section class="secao">
        <header class="secao-cab"><h2><?= e(t('more.title')) ?></h2></header>
        <div class="mais">
            <a class="mais-item" href="<?= e($L['nekyoh']) ?>" target="_blank" rel="noopener">
                <strong>nekyoh.tech <?= ic('external') ?></strong><span><?= e(t('more.nekyoh')) ?></span>
            </a>
            <a class="mais-item" href="<?= e($L['epiccraft']) ?>" target="_blank" rel="noopener">
                <strong>Epic Craft <?= ic('external') ?></strong><span><?= e(t('more.epiccraft')) ?></span>
            </a>
            <a class="mais-item mais-yt" href="<?= e($L['tekyoh']) ?>" target="_blank" rel="noopener">
                <strong><?= ic('youtube') ?> Tekyoh</strong><span><?= e(t('more.tekyoh')) ?></span>
            </a>
            <a class="mais-item mais-yt" href="<?= e($L['nezoru']) ?>" target="_blank" rel="noopener">
                <strong><?= ic('youtube') ?> Nezoru</strong><span><?= e(t('more.nezoru')) ?></span>
            </a>
        </div>
    </section>
</main>

<footer class="rodape">
    <div>
        <p><strong>NekyLauncher</strong> by Nekyoh · <?= e(t('footer.by', ['a' => implode(' & ', $CFG['autores'])])) ?></p>
        <p><?= e(t('footer.lic')) ?></p>
    </div>
    <p class="rodape-aviso"><?= e(t('footer.disc')) ?></p>
</footer>
</body>
</html>
