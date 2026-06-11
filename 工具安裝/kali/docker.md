# Kali Linux 安裝 Docker + Docker Compose v2 的完整 sh

## SHELL

``` bash
#!/usr/bin/env bash
set -euo pipefail

# ============================================================
# Kali Linux Docker + Docker Compose v2 Installer
# 適用：Kali Linux / Kali Rolling
# 安裝：
#   chmod +x install-docker-kali.sh
#   sudo ./install-docker-kali.sh
#
# 驗證：
#   docker --version
#   docker compose version
#   docker run --rm hello-world
# ============================================================

if [ "$(id -u)" -ne 0 ]; then
  echo "[ERROR] 請用 root 或 sudo 執行：sudo $0"
  exit 1
fi

REAL_USER="${SUDO_USER:-$(logname 2>/dev/null || echo root)}"

echo "==> [1/8] 檢查系統資訊"
if [ -f /etc/os-release ]; then
  . /etc/os-release
  echo "系統：${PRETTY_NAME:-unknown}"
fi

echo "==> [2/8] 更新 apt 套件索引"
apt update

echo "==> [3/8] 安裝必要工具"
apt install -y \
  ca-certificates \
  curl \
  gnupg \
  lsb-release \
  uidmap

echo "==> [4/8] 移除可能衝突的舊套件"
# Docker 官方文件也建議先移除可能衝突的 docker / compose / containerd / runc 套件
apt remove -y \
  docker \
  docker-engine \
  docker.io \
  docker-doc \
  docker-compose \
  podman-docker \
  containerd \
  runc \
  2>/dev/null || true

echo "==> [5/8] 安裝 Kali 官方 Docker Engine 套件 docker.io"
apt update
apt install -y docker.io

echo "==> [6/8] 安裝 Docker Compose v2 plugin"
if apt-cache show docker-compose-plugin >/dev/null 2>&1; then
  apt install -y docker-compose-plugin
else
  echo "[WARN] apt 找不到 docker-compose-plugin，改用 GitHub 官方 binary 安裝 Compose plugin"

  ARCH="$(uname -m)"
  case "$ARCH" in
    x86_64)
      COMPOSE_ARCH="x86_64"
      ;;
    aarch64 | arm64)
      COMPOSE_ARCH="aarch64"
      ;;
    armv7l)
      COMPOSE_ARCH="armv7"
      ;;
    *)
      echo "[ERROR] 不支援的 CPU 架構：$ARCH"
      exit 1
      ;;
  esac

  COMPOSE_VERSION="$(curl -fsSL https://api.github.com/repos/docker/compose/releases/latest \
    | grep -Po '"tag_name":\s*"\K[^"]+')"

  mkdir -p /usr/local/lib/docker/cli-plugins

  curl -fL \
    "https://github.com/docker/compose/releases/download/${COMPOSE_VERSION}/docker-compose-linux-${COMPOSE_ARCH}" \
    -o /usr/local/lib/docker/cli-plugins/docker-compose

  chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
fi

echo "==> [7/8] 啟用並啟動 Docker 服務"
systemctl enable docker
systemctl restart docker

echo "==> [8/8] 將目前使用者加入 docker 群組"
if [ "$REAL_USER" != "root" ]; then
  usermod -aG docker "$REAL_USER"
  echo "[INFO] 已將 $REAL_USER 加入 docker 群組"
  echo "[INFO] 請登出再登入，或執行：newgrp docker"
fi

echo
echo "==> 安裝完成，版本資訊如下："
docker --version || true
docker compose version || true

echo
echo "==> 執行 hello-world 測試"
docker run --rm hello-world

echo
echo "============================================================"
echo "完成。常用指令："
echo "  docker ps"
echo "  docker images"
echo "  docker compose version"
echo "  docker compose up -d"
echo "  systemctl status docker"
echo
echo "若非 root 使用 docker 出現 permission denied："
echo "  1. 登出再登入"
echo "  2. 或執行：newgrp docker"
echo "============================================================"
```
## 執行

``` bash
nano install-docker-kali.sh
chmod +x install-docker-kali.sh
sudo ./install-docker-kali.sh
```

## 驗證

``` bash
docker --version
docker compose version
docker run --rm hello-world
```