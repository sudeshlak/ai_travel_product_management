#!/bin/sh
set -e

cd /app

if [ ! -d node_modules/vite ] || [ package-lock.json -nt node_modules ]; then
  echo "Installing frontend dependencies..."
  npm ci
fi

exec "$@"
