# Phase 7: cPanel pull deployment

The checked-in `.cpanel.yml` targets the client-confirmed WordPress document root:

```text
/home/holidayk/public_html
```

It creates the WordPress theme and plugin parent directories when necessary, then
copies only:

```text
wp-content/themes/hks-wayfinder
wp-content/plugins/hks-core
```

It does not deploy WordPress core, uploads, `wp-config.php`, databases, repository
documentation, source work, or Git metadata.

## Initial clone

In cPanel **Files → Git Version Control → Create**:

- Clone URL: `https://github.com/Allan-Gakuru/HolidayKenyaSafaris.git`
- Repository Path: `/home/holidayk/repositories/HolidayKenyaSafaris`
- Repository Name: `HolidayKenyaSafaris`

Keep the repository outside `public_html`.

If GitHub requires authentication, configure a repository-specific read-only
deploy key and use its SSH clone URL. Do not put a token or private key in this
repository or `.cpanel.yml`.

## Deploy

From the repository's cPanel **Manage → Pull or Deploy** screen:

1. Select **Update from Remote**.
2. Confirm the expected `main` HEAD commit.
3. Select **Deploy HEAD Commit**.
4. Review deployment output for failed commands.
5. Confirm the two destination directories exist under WordPress.

Repeat the update and deploy actions for future GitHub releases. The copy tasks do
not activate WordPress extensions or alter database content.

Run `python tools/validate_cpanel_deployment.py` before changing the deployment
manifest.
