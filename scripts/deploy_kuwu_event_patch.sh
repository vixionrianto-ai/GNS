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

if ! docker exec gns-php php -v >/dev/null 2>&1; then
  echo "ERROR: container gns-php tidak berjalan atau PHP tidak tersedia."
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
  "app/Services/OltMonitoringService.php"
  "app/Console/Commands/OltMonitorCommand.php"
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
  docker exec -i gns-php php -l < "$TMP/$file"
done

echo "==> Memasang lima file KUWU..."
for file in "${FILES[@]}"; do
  mkdir -p "$ROOT/$(dirname "$file")"
  install -m 0644 "$TMP/$file" "$ROOT/$file"
done

echo "==> Memeriksa file setelah dipasang..."
if ! docker exec -w /config/www gns-php php -l app/Http/Controllers/Api/OltEventController.php ||
   ! docker exec -w /config/www gns-php php -l app/Jobs/ProcessOltDisconnect.php ||
   ! docker exec -w /config/www gns-php php -l app/Jobs/ProcessOltRecovery.php ||
   ! docker exec -w /config/www gns-php php -l app/Services/OltMonitoringService.php ||
   ! docker exec -w /config/www gns-php php -l app/Console/Commands/OltMonitorCommand.php; then
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
echo "BERHASIL: lima file KUWU terpasang dan lolos php -l."
echo "Backup lama: $BACKUP"
echo "Belum ada container yang direstart."
