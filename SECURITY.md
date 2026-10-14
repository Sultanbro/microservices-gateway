# Credentials and local development

Keep runtime credentials in the environment or a secret manager. Never commit
real values to example files, source code, Dockerfiles, logs, generated config,
or files served by the web server. Use dummy credentials for tests.

Install [Gitleaks](https://github.com/gitleaks/gitleaks), then enable the local
commit guard in each clone:

```sh
git config core.hooksPath .githooks
```

The guard checks staged changes and refuses commits if the scanner is missing
or detects a secret. Run a full check when reviewing existing code:

```sh
gitleaks dir --redact=100 .
gitleaks git --log-opts="--all" --redact=100 .
```

Removing a value in a new commit does not remove previous commits. If a secret
was exposed, revoke or rotate it at its provider and update affected deployments.
Coordinate any history rewrite with repository collaborators before publishing it.
Do not put secret values into issues, pull requests, or commit messages.

## Signing keys

The previously committed OAuth signing key pair must be treated as compromised.
If any deployment used it, rotate the pair in that deployment and invalidate
affected tokens. Mount replacement keys at runtime; never add them to Git or
the Docker build context. This repository does not generate replacement keys.
Set `PUSHER_APP_SECRET` only in the backend runtime environment.

Gateway access is denied unless a non-empty `ALLOWED_SECRETS` value is configured.
Dependencies are installed from `composer.lock`; the vendor directory is not versioned.
