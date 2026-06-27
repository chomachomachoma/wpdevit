# Deployment

Production runs on a DigitalOcean droplet (`marketplace-s-1vcpu-1gb-nyc1`,
142.93.246.163) serving `https://wpdevit.com` from
`/var/www/wpdevit.com` (Apache + mod_php 8.4 + MySQL 8, Let's Encrypt SSL).

## Automated deploys (GitHub Actions)

`.github/workflows/deploy.yml` builds the Svelte SPA and ships the compiled
theme to the droplet on every push to `main` that touches theme source. It can
also be run on demand from the repo's **Actions** tab ("Run workflow").

What it does: `npm ci` → `npm run build` → stage runtime files
(`functions.php`, `index.php`, `style.css`, `inc/`, `dist/`) → `rsync --delete`
to the live theme dir → `chown www-data` → smoke-test the homepage and REST.

Only the theme is deployed. WordPress core, plugins, uploads, and the database
already live on the droplet and are not touched by CI.

### One-time setup: add the deploy key as a secret

The workflow authenticates to the droplet with the `chrischoma` SSH key (already
authorized for `root` on the droplet). Add its **private** key as a repository
secret named `DO_SSH_KEY`:

1. GitHub → repo **Settings** → **Secrets and variables** → **Actions** →
   **New repository secret**.
2. Name: `DO_SSH_KEY`
3. Value: the full contents of `~/.ssh/chrischoma` (the private key, including
   the `-----BEGIN/END-----` lines).
4. Save.

Host, user, and theme path are non-sensitive and set as `env:` in the workflow;
edit them there if the server ever changes.

## Manual deploy (equivalent to what CI runs)

```bash
npm run build
rm -rf deploy && mkdir deploy
cp -r functions.php index.php style.css inc dist deploy/
rsync -az --delete -e "ssh -i ~/.ssh/chrischoma -o IdentitiesOnly=yes" \
  deploy/ root@142.93.246.163:/var/www/wpdevit.com/wp-content/themes/wpdevit/
ssh -i ~/.ssh/chrischoma root@142.93.246.163 \
  "chown -R www-data:www-data /var/www/wpdevit.com/wp-content/themes/wpdevit"
```
