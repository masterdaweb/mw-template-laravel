# Laravel na Master da Web

Um app Laravel 13 com tudo o que um projeto real precisa, publicado com um clique pela galeria de templates do console.

| Serviço | O que roda |
| --- | --- |
| `web` | o site, no FrankenPHP; `php artisan migrate --force` roda uma vez por deploy, antes de o tráfego virar |
| `worker` | `php artisan queue:work`: os jobs que o seu código despacha |
| `scheduler` | `php artisan schedule:run`, a cada minuto |

Os três compartilham o PostgreSQL (`DB_*`), o Redis (`REDIS_*`: fila, cache e sessões) e um bucket público do Object Storage (`AWS_*`, o disco `s3`), injetados pelo projeto: nada para copiar à mão. O `APP_KEY` foi gerado para você.

A página inicial mostra cada parte funcionando: envie um arquivo e ele vai para o Object Storage, ganha uma linha no PostgreSQL e um job que o worker processa; o agendador grava um sinal no Redis a cada minuto. `GET /status.json` dá as mesmas verificações para um monitor.

## Torne o código seu

1. No console, abra o projeto e siga **Seu código**: copie este template
   para o seu GitHub e conecte a cópia ao projeto.
2. Altere `routes/web.php` e `resources/views/welcome.blade.php` e faça push: cada push faz um deploy, sem tirar o
   app do ar.

## Rodar na sua máquina

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan serve
```

## Bom saber

- Arquivos gravados no disco do container somem no próximo deploy: guarde uploads no disco `s3`, como este app faz.
- Vai adicionar o Vite (`package.json`)? O build passa a guardar a configuração em cache; os comandos do worker e do agendador começam com `php artisan config:clear` para lerem as variáveis do projeto.

*[English](README.en.md)*
