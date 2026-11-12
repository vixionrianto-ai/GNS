#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="/DATA/AppData/nginx/config/www"
BRANCH="fix/kuwu-telegram-event-flow-2026-11-12"
cd "$ROOT"

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "ERROR: folder ini bukan working tree Git: $ROOT"
  exit 1
fi

if ! git remote get-url origin >/dev/null 2>&1; then
  echo "ERROR: remote origin tidak ditemukan. Tidak ada file yang diubah."
  exit 1
fi

echo "==> Mengambil branch patch dari GitHub..."
git fetch origin "$BRANCH"

STAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP="$ROOT/storage/app/backups/kuwu-event-$STAMP"
TMP="$(mktemp -d)"
mkdir -p "$BACKUP"

FILES=(
  "app/Http/Controllers/Api/OltEventController.php"
  "app/Jobs/ProcessOltDisconnect.php"
  "app/Jobs/ProcessOltRecovery.php"
)

cleanup() { rm -rf "$TMP"; }
trap cleanup EXIT

echo "==> Backup file produksi..."
for file in "${FILES[@]}"; do
  mkdir -p "$BACKUP/$(dirname "$file")"
  if [ -f "$ROOT/$file" ]; then
    cp -a "$ROOT/$file" "$BACKUP/$file"
    echo "BACKUP $file"
  else
    touch "$BACKUP/.__MISSING__.$(echo "$file" | tr '/' '_')"
    echo "BARU   $file"
  fi
done

echo "==> Menyiapkan file dari branch $BRANCH..."
for file in "${FILES[@]}"; do
  mkdir -p "$TMP/$(dirname "$file")"
  git show "origin/$BRANCH:$file" > "$TMP/$file"
  php -l "$TMP/$file"
done

echo "==> Memasang tiga file KUWU..."
for file in "${FILES[@]}"; do
  mkdir -p "$ROOT/$(dirname "$file")"
  install -m 0644 "$TMP/$file" "$ROOT/$file"
done

echo "==> Memeriksa file setelah dipasang..."
if ! php -l "$ROOT/app/Http/Controllers/Api/OltEventController.php" ||
   ! php -l "$ROOT/app/Jobs/ProcessOltDisconnect.php" ||
   ! php -l "$ROOT/app/Jobs/ProcessOltRecovery.php"; then
  echo "ERROR: pemeriksaan gagal. Mengembalikan backup..."
  for file in "${FILES[@]}"; do
    if [ -f "$BACKUP/$file" ]; then
      cp -a "$BACKUP/$file" "$ROOT/$file"
    else
      rm -f "$ROOT/$file"
    fi
  done
  echo "ROLLBACK selesai. Backup: $BACKUP"
  exit 1
fi

echo
echo "BERHASIL: tiga file KUWU terpasang dan lolos php -l."
echo "Backup lama: $BACKUP"
echo "Belum ada container yang direstart."
