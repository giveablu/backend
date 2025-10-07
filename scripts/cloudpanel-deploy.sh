#!/bin/bash
set -euo pipefail

# CloudPanel Git hook for Laravel deployments.
# Copy this file to /home/cloudpanel/htdocs/<site>/hooks/deploy on your server.
# Adjust paths, PHP binary, and commands to match your environment before using.

APP_DIR="/home/blu-service/htdocs/service.blu.gives"
PHP_BIN="/usr/bin/php"
COMPOSER_BIN="/usr/local/bin/composer"
BRANCH="main"

log() {
  printf '[deploy] %s\n' "$1"
}

log "Starting deployment"
cd "$APP_DIR"

if [ ! -d .git ]; then
  log "No Git repository found in $APP_DIR"
  log "Ensure the project is cloned and .git directory exists before running this hook"
  exit 1
fi

log "Putting application into maintenance mode"
$PHP_BIN artisan down || log "Application already in maintenance mode"

log "Fetching latest changes from $BRANCH"
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"

dep_cache_dir="storage/framework/cache/data"
if [ ! -d "$dep_cache_dir" ]; then
  log "Creating cache directory $dep_cache_dir"
  mkdir -p "$dep_cache_dir"
fi

log "Installing composer dependencies"
$COMPOSER_BIN install --no-dev --optimize-autoloader

log "Running database migrations"
$PHP_BIN artisan migrate --force

log "Clearing caches"
$PHP_BIN artisan cache:clear
$PHP_BIN artisan config:clear
$PHP_BIN artisan route:clear
$PHP_BIN artisan view:clear

log "Optimizing application"
$PHP_BIN artisan optimize

log "Restarting queues"
$PHP_BIN artisan queue:restart || true

log "Bringing application out of maintenance"
$PHP_BIN artisan up

log "Deployment finished successfully"
