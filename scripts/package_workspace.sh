#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SRC_DIR="${1:-${ROOT_DIR}/workspace/myoverview}"
BUILD_DIR="${ROOT_DIR}/build"
STAMP="$(date +%Y%m%d_%H%M%S)"
OUT_ZIP="${BUILD_DIR}/myoverview_${STAMP}.zip"
STAGE_DIR="$(mktemp -d)"

if [[ ! -d "${SRC_DIR}" ]]; then
  echo "ERROR: workspace plugin not found: ${SRC_DIR}" >&2
  echo "Ensure workspace/myoverview exists." >&2
  exit 1
fi

mkdir -p "${BUILD_DIR}"

cleanup() {
  rm -rf "${STAGE_DIR}"
}
trap cleanup EXIT

# Moodle expects block folder name inside blocks/ as "myoverview" (not "block_myoverview").
cp -a "${SRC_DIR}" "${STAGE_DIR}/myoverview"

(
  cd "${STAGE_DIR}"
  zip -rq "${OUT_ZIP}" "myoverview" -x '*.DS_Store' '*__MACOSX*' '*/.git/*'
)

echo "OK: package created"
echo "  ${OUT_ZIP}"
