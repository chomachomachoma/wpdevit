# Deployment

Production runs on a DigitalOcean droplet (`marketplace-s-1vcpu-1gb-nyc1`,
142.93.246.163) serving `https://wpdevit.com` from
`/var/www/wpdevit.com` (Apache + mod_php 8.4 + MySQL 8, Let's Encrypt SSL,
Cloudflare in front).

## Automated deploys (GitHub Actions, self-hosted runner)

`.github/workflows/deploy.yml` builds the Svelte SPA and deploys the compiled
theme on every push to `main` that touches theme source. It can also be run on
demand from the repo's **Actions** tab ("Run workflow").

**Why self-hosted:** a DigitalOcean Cloud Firewall blocks inbound SSH (port 22)
from GitHub-hosted runners, so we can't `rsync` in from the cloud. Instead a
**self-hosted runner runs on the droplet itself** — it pulls jobs over outbound
HTTPS and deploys with a local file copy. No inbound SSH, no SSH key secret.

What the job does (all on the droplet): `npm ci` → `npm run build` → stage
runtime files (`functions.php`, `index.php`, `style.css`, `inc/`, `dist/`) →
local `rsync --delete` into the live theme dir → `chown www-data` →
smoke-test the homepage and REST endpoint.

Only the theme is deployed. WordPress core, plugins, uploads, and the database
live on the droplet and are not touched by CI.

### The runner

Installed at `/opt/actions-runner` on the droplet and managed by systemd:

```bash
# status / logs / restart
systemctl status 'actions.runner.*'
journalctl -u 'actions.runner.*' -f
cd /opt/actions-runner && sudo ./svc.sh stop|start
```

Labels: `self-hosted, droplet, production`. The workflow targets
`runs-on: [self-hosted, droplet]`. The runner runs as `root` so it can write
the theme dir and `chown` to `www-data`; to harden later, run it as a dedicated
user with targeted `sudo` for the deploy + chown.

To re-register the runner if it's ever removed: generate a registration token
(repo → Settings → Actions → Runners → New self-hosted runner), then
`cd /opt/actions-runner && sudo ./config.sh remove` and re-run `config.sh`.

## Manual deploy (local file copy on the droplet)

```bash
ssh root@142.93.246.163
cd /var/www/wpdevit.com/wp-content/themes/wpdevit   # or a fresh checkout
npm ci && npm run build
rm -rf deploy && mkdir deploy
cp -r functions.php index.php style.css inc dist deploy/
rsync -a --delete deploy/ /var/www/wpdevit.com/wp-content/themes/wpdevit/
chown -R www-data:www-data /var/www/wpdevit.com/wp-content/themes/wpdevit
```
