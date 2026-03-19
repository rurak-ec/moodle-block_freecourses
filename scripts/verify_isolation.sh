#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ERRORS=0

echo "Isolation check for: ${ROOT_DIR}"

for dir in workspace scripts docs; do
  if [[ ! -d "${ROOT_DIR}/${dir}" ]]; then
    echo "ERROR: missing directory ${ROOT_DIR}/${dir}" >&2
    ERRORS=$((ERRORS + 1))
  fi
done

if [[ -d "${ROOT_DIR}/workspace/myoverview" ]]; then
  echo "OK: workspace/myoverview found"
else
  echo "ERROR: workspace/myoverview not found" >&2
  ERRORS=$((ERRORS + 1))
fi

if [[ -f "${ROOT_DIR}/workspace/myoverview/version.php" ]]; then
  echo "OK: version.php present"
else
  echo "ERROR: version.php missing" >&2
  ERRORS=$((ERRORS + 1))
fi

if [[ -f "${ROOT_DIR}/workspace/myoverview/block_myoverview.php" ]]; then
  echo "OK: block_myoverview.php (main block class) present"
else
  echo "ERROR: block_myoverview.php missing" >&2
  ERRORS=$((ERRORS + 1))
fi

if [[ ${ERRORS} -gt 0 ]]; then
  echo "FAIL: ${ERRORS} error(s) detected" >&2
  exit 1
fi

echo "OK: base structure looks good for development."
