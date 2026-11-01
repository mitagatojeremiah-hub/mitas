# Deployment Guide for Mitas Business Online Hub

This repository includes a lightweight PHP + SQLite application and the files needed to host it locally, on Render, and with encrypted backups.

## Local Docker deployment

1. Build and run the container:

```bash
cd c:\xamppc\htdocs\mitas
docker compose build
docker compose up -d
```

2. Visit:

```text
http://localhost:8080
```

## Public link with ngrok

1. Install ngrok from https://ngrok.com.
2. Run:

```bash
ngrok http 8080
```

3. Use the HTTPS URL shown by ngrok.

## GitHub Actions CI / Docker build

A workflow is included at `.github/workflows/docker-build.yml`.

It:
- builds the Docker image on push or PR to `main`/`master`
- validates PHP syntax for all `*.php` files
- optionally publishes the image when registry secrets are configured

### Optional secrets for publishing

- `REGISTRY` � registry host (e.g. `docker.io`)
- `REGISTRY_USERNAME`
- `REGISTRY_PASSWORD`
- `IMAGE_NAME` � image name, e.g. `username/mitas-business-online-hub`

## Render deployment

A `render.yaml` manifest is included for Render.com.

To deploy:

1. Push this repo to GitHub.
2. Create a new Web Service on Render.
3. Select Docker and connect your repo.
4. Use branch `main` and this `render.yaml` file.

Render will build the Dockerfile and serve the container with automatic TLS.

## Encrypted S3 backups

A new endpoint `s3_backup.php` uploads the SQLite database to AWS S3.

### Important note

Use `ngrok http 8080` (not grok) when creating a public tunnel.

### Required environment variables

- `S3_BUCKET` � S3 bucket name
- `AWS_REGION` � AWS region (default `us-east-1`)
- `S3_PREFIX` � optional path prefix, default `backups/`

### Notes

- `s3_backup.php` requires Composer dependencies installed in the container.
- The upload uses server-side AES256 encryption.
- Use an IAM role or credentials with minimal S3 write privileges.

### Recommended IAM policy

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:PutObject",
        "s3:PutObjectAcl"
      ],
      "Resource": "arn:aws:s3:::YOUR_BUCKET_NAME/backups/*"
    }
  ]
}
```

## Notes on production readiness

- SQLite is fine for small teams and prototypes.
- For higher concurrency and reliability, plan a migration to MySQL/PostgreSQL.
- Ensure `data/` is writable by the web server and back up `data/mitas_system.sqlite` regularly.
