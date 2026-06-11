# Kali 安裝 OpenCode TUI 版 的完整 sh

## SHELL

``` bash
#!/usr/bin/env bash
set -euo pipefail

# ============================================================
# Kali Linux OpenCode TUI Installer
# 安裝 OpenCode 終端機 TUI 版
#
# 使用：
#   chmod +x install-opencode-kali.sh
#   ./install-opencode-kali.sh
#
# 啟動：
#   opencode
#   opencode /path/to/project
# ============================================================

echo "==> [1/7] 檢查系統"

if [ -f /etc/os-release ]; then
  . /etc/os-release
  echo "系統：${PRETTY_NAME:-unknown}"
fi

if ! command -v sudo >/dev/null 2>&1; then
  echo "[ERROR] 找不到 sudo，請先用 root 安裝 sudo"
  exit 1
fi

echo "==> [2/7] 更新 apt"
sudo apt update

echo "==> [3/7] 安裝必要工具"
sudo apt install -y \
  curl \
  ca-certificates \
  git \
  unzip \
  xz-utils \
  build-essential

echo "==> [4/7] 安裝 Node.js / npm"
if ! command -v node >/dev/null 2>&1; then
  sudo apt install -y nodejs npm
else
  echo "已安裝 Node.js：$(node --version)"
fi

if ! command -v npm >/dev/null 2>&1; then
  sudo apt install -y npm
else
  echo "已安裝 npm：$(npm --version)"
fi

echo "==> [5/7] 嘗試使用 OpenCode 官方 install script 安裝"
if curl -fsSL https://opencode.ai/install | bash; then
  echo "官方 install script 安裝完成"
else
  echo "[WARN] 官方 install script 失敗，改用 npm 安裝"
  sudo npm install -g opencode-ai
fi

echo "==> [6/7] 修正 PATH"

# 常見安裝位置
POSSIBLE_PATHS=(
  "$HOME/.opencode/bin"
  "$HOME/.local/bin"
  "$HOME/.bun/bin"
  "/usr/local/bin"
)

for p in "${POSSIBLE_PATHS[@]}"; do
  if [ -d "$p" ]; then
    case ":$PATH:" in
      *":$p:"*) ;;
      *)
        export PATH="$p:$PATH"
        ;;
    esac
  fi
done

# 寫入 shell 設定，避免重開終端後找不到 opencode
SHELL_RC=""

if [ -n "${ZSH_VERSION:-}" ] || [ "$(basename "${SHELL:-}")" = "zsh" ]; then
  SHELL_RC="$HOME/.zshrc"
else
  SHELL_RC="$HOME/.bashrc"
fi

for p in "${POSSIBLE_PATHS[@]}"; do
  if [ -d "$p" ]; then
    if ! grep -q "export PATH=\"$p:\$PATH\"" "$SHELL_RC" 2>/dev/null; then
      echo "export PATH=\"$p:\$PATH\"" >> "$SHELL_RC"
    fi
  fi
done

echo "==> [7/7] 驗證安裝"

if command -v opencode >/dev/null 2>&1; then
  echo "OpenCode 路徑：$(command -v opencode)"
  opencode --version || true
else
  echo "[ERROR] 找不到 opencode 指令"
  echo
  echo "請嘗試："
  echo "  source ~/.bashrc"
  echo "或："
  echo "  source ~/.zshrc"
  echo
  echo "若仍失敗，手動執行："
  echo "  sudo npm install -g opencode-ai"
  exit 1
fi

echo
echo "============================================================"
echo "OpenCode TUI 安裝完成"
echo
echo "啟動目前目錄："
echo "  opencode"
echo
echo "啟動指定專案："
echo "  opencode /path/to/project"
echo
echo "進入 TUI 後建議先執行："
echo "  /connect"
echo "  /models"
echo
echo "如果使用 ChatGPT Plus / Pro："
echo "  /connect -> OpenAI -> ChatGPT Plus/Pro"
echo
echo "若 opencode 指令找不到，重開 Terminal 或執行："
echo "  source ~/.bashrc"
echo "============================================================"
```

## 執行

``` bash
nano install-opencode-kali.sh
chmod +x install-opencode-kali.sh
./install-opencode-kali.sh
```

### 啟動 TUI

``` bash
# opencode /path/to/project
opencode 
```