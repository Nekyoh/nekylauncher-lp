<p align="center">
  <img src="assets/img/logo-256.png" alt="NekyLauncher" width="160">
</p>

# NekyLauncher — site de download

Código da página **https://launcher.nekyoh.tech**: apresentação do launcher e download da versão mais recente, que vem direto do [GitHub Releases](https://github.com/Nekyoh/nekylauncher/releases).

- Em **português, inglês e espanhol** (mesmo esquema do nekyoh.tech: `?lang=pt|en|es`, cookie `nekyoh_lang` ou o idioma do navegador).
- Descobre se a pessoa está no **Windows ou Linux** e já mostra o botão certo; no Mac ou no celular explica que ainda não há versão.
- **Versão, tamanhos, links e novidades** são lidos do GitHub pelo servidor e guardados em `cache/` por 10 minutos. Se o GitHub cair, a página continua com a última resposta boa.
- **Nada de JavaScript**: só PHP, HTML e CSS.

## Publicar uma versão nova do launcher

Não precisa mexer aqui. Assim que a Release é **publicada** no GitHub, a página mostra a versão nova em até 10 minutos, com os 4 arquivos:

| Tipo | Arquivo reconhecido |
|---|---|
| Windows — instalador | `*.exe` |
| Windows — portátil | `*windows-x64.zip` |
| Linux — .deb | `*.deb` |
| Linux — portátil | `*linux-x64.tar.gz` |

As **novidades** da página são o texto da Release no GitHub (no idioma em que você escrever).


## Estrutura

```
index.php            a página
download.php         links fixos /download/<tipo>
config.php           repositório, links e tempo de cache (edite aqui)
lang/{pt,en,es}.php  textos
inc/bootstrap.php    idioma, sistema do visitante, cabeçalhos de segurança
inc/github.php       consulta ao GitHub com cache + notas da versão
assets/              CSS, logo, ícones e capturas de tela (uma por idioma)
cache/               resposta do GitHub (gerada no servidor, fora do git)
```

## Licença

Código sob a licença [MIT](LICENSE). Fontes Pixelify Sans e Inter (Google Fonts, OFL).

Não é um produto oficial do Minecraft. Não aprovado nem associado à Mojang ou à Microsoft.
