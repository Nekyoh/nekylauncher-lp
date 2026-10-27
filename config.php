<?php
/*
 * ============================================================
 *  NekyLauncher — site de download (launcher.nekyoh.tech)
 *  Links e opções ficam aqui. Os TEXTOS ficam em lang/*.php
 * ============================================================
 */
return [
    'nome'    => 'NekyLauncher',
    // Endereço público do site (usado no link canônico e no sitemap)
    'url'     => 'https://launcher.nekyoh.tech',

    'autores' => ['Tekyoh', 'Nezoru'],

    // Repositório no GitHub de onde vêm a versão, os downloads e as novidades
    'repo' => 'Nekyoh/nekylauncher',

    // Quanto tempo (segundos) guardar a resposta do GitHub antes de perguntar de novo.
    // 10 min = no máximo 6 consultas por hora, bem abaixo do limite de 60 do GitHub.
    'cache_ttl' => 600,

    // Opcional: token do GitHub (só leitura de repositórios públicos) para subir o limite
    // para 5000/hora. Deixe vazio; prefira a variável de ambiente GITHUB_TOKEN no servidor.
    'github_token' => '',

    'idiomas'       => ['pt' => 'Português', 'en' => 'English', 'es' => 'Español'],
    'idioma_padrao' => 'en',   // quando o navegador não pede pt/en/es

    'links' => [
        'github'   => 'https://github.com/Nekyoh/nekylauncher',
        'releases' => 'https://github.com/Nekyoh/nekylauncher/releases',
        'issues'   => 'https://github.com/Nekyoh/nekylauncher/issues',
        'nekyoh'   => 'https://nekyoh.tech',
        'epiccraft'=> 'https://epiccraft.nekyoh.tech',
        'tekyoh'   => 'https://www.youtube.com/@Tekyoh01',
        'nezoru'   => 'https://www.youtube.com/@Nezoru',
    ],
];
