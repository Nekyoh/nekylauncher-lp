<?php
/*
 *  Links fixos que sempre levam ao arquivo da versão mais nova:
 *    https://launcher.nekyoh.tech/download/windows        → instalador .exe
 *    https://launcher.nekyoh.tech/download/windows-zip    → Windows portátil
 *    https://launcher.nekyoh.tech/download/linux          → pacote .deb
 *    https://launcher.nekyoh.tech/download/linux-tar      → Linux portátil
 *  (sem mod_rewrite: download.php?f=windows)
 *  Bom para colocar no README, no nekyoh.tech ou em vídeos.
 */
$CFG = require __DIR__ . '/config.php';
require __DIR__ . '/inc/github.php';

$apelidos = [
    'windows' => 'windows-installer', 'windows-installer' => 'windows-installer', 'exe' => 'windows-installer',
    'windows-zip' => 'windows-portable', 'windows-portable' => 'windows-portable', 'zip' => 'windows-portable',
    'linux' => 'linux-deb', 'linux-deb' => 'linux-deb', 'deb' => 'linux-deb',
    'linux-tar' => 'linux-portable', 'linux-portable' => 'linux-portable', 'tar' => 'linux-portable',
];
$tipo = $apelidos[strtolower((string)($_GET['f'] ?? ''))] ?? null;
$rel  = nl_ultima_versao($CFG);
$url  = ($tipo && $rel && isset($rel['arquivos'][$tipo])) ? $rel['arquivos'][$tipo]['url'] : $CFG['links']['releases'];

header('Cache-Control: no-store');
header('Location: ' . $url, true, 302);
