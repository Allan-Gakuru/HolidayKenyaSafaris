# Local to GitHub to cPanel Deployment Pipeline

## Objective

Create a repeatable release path:

`local working directory -> GitHub pull request -> automated tests/build -> cPanel staging -> production approval -> cPanel production`

GitHub is the canonical code repository. cPanel hosts WordPress. Production WordPress content, uploads, configuration, and databases are persistent server data and are not replaced during ordinary code deployments.

## Recommended Release Model

- Work in feature branches locally.
- Open a pull request into `main`.
- Run CI on every pull request.
- Merge to `main` only after CI passes.
- Deploy `main` automatically to a password-protected, `noindex` staging site.
- Deploy a tested commit or version tag to production through a manually triggered workflow or protected GitHub production environment.
- Record the deployed commit SHA for staging and production.

GitHub Environments can separate staging and production secrets and optionally add deployment approvals. Availability of required-reviewer protection for private repositories depends on the GitHub plan.

## First Decision: cPanel Capability

In cPanel, confirm whether these interfaces are available:

- `Security -> SSH Access`.
- `Files -> Git Version Control`.
- Terminal or shell access.
- PHP version selection and required extensions.
- MySQL/MariaDB database management.
- SSL/TLS.
- Cron jobs.
- Backup or hosting-provider snapshot capability.

### Preferred transport

Use GitHub Actions with a dedicated SSH key and `rsync` or an equivalent server-side copy when SSH is available. This gives the clearest automated staging and production workflow.

### Native cPanel Git alternative

cPanel Git Version Control supports deployment through a checked-in `.cpanel.yml` file:

- Pull deployment clones GitHub into cPanel, then requires `Update from Remote` and `Deploy HEAD Commit` in cPanel.
- Push deployment automatically deploys commits pushed directly to the cPanel-managed repository and is the mode cPanel recommends.
- GitHub Actions may push a tested commit to a cPanel-managed Git remote if SSH access is available.

Do not use wildcard deployment commands in `.cpanel.yml`. Deploy explicit theme and plugin paths.

### SFTP fallback

If Git and shell access are unavailable, use an SFTP account scoped as narrowly as the host permits. GitHub Actions can upload a prepared release artifact, but rollback and atomic releases will be less capable.

## Initial cPanel Setup

### Confirmed MVP pull-deployment path

The confirmed cPanel account is `holidayk` and the WordPress document root is
`/home/holidayk/public_html`. The repository-root `.cpanel.yml` copies only the
`hks-wayfinder` theme and `hks-core` plugin into that installation. The recommended
cPanel-managed clone path is `/home/holidayk/repositories/HolidayKenyaSafaris`.

Create two independent environments:

| Environment | Example | Requirements |
|---|---|---|
| Staging | `staging.example.com` | Separate document root, database, uploads, config, SSL, password protection, `noindex` |
| Production | `example.com` | Production document root, database, uploads, config, SSL, backups |

For each environment:

1. Create the domain or subdomain and document root.
2. Create a separate database and database user.
3. Install WordPress.
4. Set the supported PHP version and required extensions.
5. Configure `wp-config.php` on the server; never commit it.
6. Install Secure Custom Fields and required production plugins.
7. Configure HTTPS.
8. Confirm WordPress file ownership and permissions.
9. Configure backup and restore capability before the first automated deployment.
10. Protect staging with HTTP authentication or equivalent access control and discourage indexing.

Do not use one database for staging and production.

## Initial GitHub Setup

1. Create a private GitHub repository.
2. Push the working directory as the initial codebase.
3. Protect `main` where the GitHub plan permits.
4. Require CI to pass before merge.
5. Create `staging` and `production` GitHub Environments, or use repository secrets if private-plan environment features are unavailable.
6. Add a manual production approval or `workflow_dispatch` release step.
7. Disable direct production deployment from unreviewed branches.

Suggested secrets:

```text
CPANEL_SSH_HOST
CPANEL_SSH_PORT
CPANEL_SSH_USER
CPANEL_SSH_PRIVATE_KEY
STAGING_DEPLOY_PATH
PRODUCTION_DEPLOY_PATH
```

Use separate keys or credentials for staging and production when practical. Do not store passwords, private keys, database credentials, salts, API keys, or analytics secrets in the repository.

## SSH Key Setup

1. Generate a dedicated deployment key pair, not a developer's personal everyday key.
2. Import or add the public key under cPanel SSH Access.
3. Authorize the public key in cPanel.
4. Store the private key as an encrypted GitHub secret.
5. Record the server host key in the workflow's `known_hosts` configuration.
6. Test a non-destructive SSH connection from the workflow.
7. Rotate and revoke the key when access changes.

Use the narrowest account and filesystem permissions available from the hosting provider.

## Repository Boundaries

Commit:

- `wp-content/themes/hks-wayfinder/`.
- `wp-content/plugins/hks-core/`.
- SCF Local JSON or code-registered field definitions.
- Source JavaScript, CSS, PHP, templates, patterns, and block code.
- `composer.lock` and package-manager lockfiles.
- Build and deployment workflows.
- Import scripts, manifests, and safe seed content.
- Documentation.

Do not commit:

- `wp-config.php`.
- `.env` files containing secrets.
- Production database dumps.
- `wp-content/uploads/` as the normal production media store.
- Cache directories.
- WordPress core, unless a deliberate Composer-managed architecture is chosen.
- `node_modules/`.
- Development logs.
- Local database volumes.

Provide `.env.example` or configuration documentation with variable names but no real secrets.

## Build Artifact

CI should create a production artifact rather than uploading the entire working directory.

Typical build sequence:

1. Check out the exact commit.
2. Install locked PHP and Node dependencies.
3. Run PHP, JavaScript, CSS, and formatting checks.
4. Run automated tests.
5. Build production CSS and JavaScript.
6. Install production PHP dependencies without development packages, if Composer is used.
7. Package only `hks-wayfinder`, `hks-core`, and explicitly required deployment files.
8. Store the artifact and commit SHA in the GitHub Actions run.

Deploy the same tested artifact to staging and production. Do not rebuild a different production artifact after staging approval.

## Deployment Scope

Ordinary code deployment should update only:

```text
wp-content/themes/hks-wayfinder/
wp-content/plugins/hks-core/
```

It may also update explicitly managed MU plugins or configuration-independent files when documented.

It must not overwrite:

```text
wp-config.php
wp-content/uploads/
the production database
server-generated cache configuration
unrelated plugins or themes
```

After deployment:

- Put WordPress into maintenance mode only when required and for the shortest practical period.
- Clear relevant WordPress, object, page, and CDN caches.
- Run safe database migrations or SCF synchronization deliberately.
- Verify the homepage, one Tour, one Campaign, the quote form, and WhatsApp launch.
- Record success or fail the workflow.

## WordPress Content and Database Policy

Git deploys code. It does not automatically deploy live WordPress content.

- SCF field definitions travel through Git.
- Tours, Campaigns, users, settings, and media attachment records live in the database.
- Photographs live in `wp-content/uploads` or an approved object-storage/CDN system.
- Production becomes the authoritative content database after launch.
- Never overwrite the production database with staging as part of a routine code deployment.

For initial seeding, use reviewed WP-CLI import scripts, WordPress export/import, or a controlled migration tool. Separate code release from content migration.

## Media Pipeline

Use a reproducible pre-import structure:

```text
content/
  media/
    source/
    prepared/
  media-manifest.csv
```

The manifest should record:

- Tour or destination ID.
- Source URL.
- Local filename.
- Owner/source notes.
- Permission or usage notes.
- Credit.
- Alt text.
- Intended role: hero, gallery, vehicle, accommodation, activity, or destination.
- WordPress attachment ID after import.

The pipeline should:

1. Download or receive the selected source image.
2. Preserve an original copy.
3. Normalize filename and metadata.
4. Create a high-quality prepared file without destructive upscaling.
5. Import through WP-CLI or WordPress Media Library.
6. Let WordPress generate responsive sizes.
7. Associate the attachment with the structured Tour or Destination fields.

Media metadata does not automatically prevent template display. Editors decide which media is assigned and published.

## Workflow Files

Plan for:

```text
.github/workflows/ci.yml
.github/workflows/deploy-staging.yml
.github/workflows/deploy-production.yml
```

### CI

- Runs on pull requests and pushes to `main`.
- Installs locked dependencies.
- Lints and tests.
- Builds the production artifact.

### Staging

- Runs after successful CI on `main`.
- Deploys the tested artifact to staging.
- Performs smoke checks.

### Production

- Runs manually or from a release tag.
- Uses the production GitHub Environment or production secrets.
- Requires approval when available.
- Deploys the same artifact tested on staging.
- Performs smoke checks and records the deployed SHA.

Use workflow concurrency so two deployments cannot modify the same environment simultaneously.

## Rollback

Before the first production deployment, prove a rollback path.

Preferred:

- Store timestamped or commit-SHA release directories on the server.
- Switch a `current` symlink after a successful upload, if cPanel shell permissions allow it.
- Retain several previous releases.
- Roll back by switching to the previous release or redeploying the previous Git tag.

Fallback:

- Back up the current theme and plugin directories before replacement.
- Keep the previous GitHub artifact and deployment SHA.
- Restore database backups separately when a content/database migration caused the issue.

Code rollback does not automatically reverse WordPress database changes. Database migrations must be backward-compatible or have an explicit reversal plan.

## Initial Information Needed From The Client/Host

- Production domain.
- Staging subdomain.
- cPanel login URL.
- Whether SSH Access is enabled.
- Whether Git Version Control is enabled.
- SSH hostname and port.
- cPanel username.
- Staging and production document-root paths.
- Supported PHP versions and extensions.
- Database names/users created on the server.
- Backup and restore method.
- GitHub organization/account and repository visibility.
- GitHub plan, because environment protections differ by plan.

Do not place actual passwords or private keys in this documentation file.

## Official References

- cPanel Git setup and push/pull deployment: `https://docs.cpanel.net/knowledge-base/web-services/guide-to-git-set-up-deployment/`
- cPanel `.cpanel.yml` deployment requirements: `https://docs.cpanel.net/knowledge-base/web-services/guide-to-git-deployment/`
- cPanel Git Version Control: `https://docs.cpanel.net/cpanel/files/git-version-control/`
- cPanel SSH Access: `https://docs.cpanel.net/cpanel/security/ssh-access/`
- GitHub deployment environments: `https://docs.github.com/en/actions/concepts/workflows-and-actions/deployment-environments`
- GitHub deployment protection and secrets: `https://docs.github.com/en/actions/reference/workflows-and-actions/deployments-and-environments`
- GitHub secure use: `https://docs.github.com/en/actions/reference/security/secure-use`
