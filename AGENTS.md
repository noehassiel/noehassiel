Follow @CLAUDE.md for the project's brand, Laravel, and testing guidance.

## Laravel Cloud

This repository deploys to Laravel Cloud, in the personal organization "Noé Bandala" (`noe-bandala`).

| What | Value |
| --- | --- |
| Application | `noehassiel` (`app-a1ec8c8b-1fa9-47b2-91e7-5bf0012335bc`), region `us-east-2` |
| Repository | `noehassiel/noehassiel`, branch `master` |
| Environment | `master` (`env-a1ec8c8c-18e4-40d7-997d-cbab3ae28585`), the only one, and it is production |
| Domains | https://noehassiel.com (verified), https://noehassiel-master-7sbtkw.laravel.cloud |
| Deploys | Push-to-deploy: every push to `master` deploys to production. Cloud runs PHP 8.5; build runs `composer install --no-dev` and `npm ci && npm run build`, deploy runs `php artisan migrate --force`. |

### Using the CLI in orbs

`.agents/setup` installs the official CLI (`laravel/cloud-cli`) outside the app's `composer.json` and puts a `cloud` wrapper on `PATH`. The wrapper sets `LARAVEL_CLOUD_TOKEN`, which the CLI reads, from the `LARAVEL_CLOUD_API_TOKEN` project secret. Run `cloud`, not `./vendor/bin/cloud`, and never print the token. Add `-n` to every command, and `--json` to reads.

```shell
cloud environment:get env-a1ec8c8c-18e4-40d7-997d-cbab3ae28585 --json -n
cloud deployment:list env-a1ec8c8c-18e4-40d7-997d-cbab3ae28585 --json -n
cloud deployment:logs <deployment-id> -n
cloud environment:logs noehassiel master --hours=1 --type=application -n
cloud environment:variables env-a1ec8c8c-18e4-40d7-997d-cbab3ae28585 --action=set --key=KEY --value=VALUE -n --force
cloud deploy noehassiel master -n
cloud deploy:monitor -n
```

- Pass the environment ID rather than the name `master` to commands that take only an environment, so they cannot resolve to another application.
- Every write here touches production. Ask the user before deploying, changing environment variables or secrets, or running `cloud command:run` or `cloud tinker`.
- Environment variable and secret changes apply only after a redeploy. Monitor every deploy with `cloud deploy:monitor -n`.
- Run `cloud <command> -h -n` for options; the `deploying-to-cloud` skill covers the rest.
