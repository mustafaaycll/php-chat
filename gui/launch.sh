#!/bin/bash
# launch.sh → run GUI app with username prompt
# - creates venv if not exists
# - installs requirements if venv created
# - asks username and runs main.py

set -e  # stop on error

# Move to script dir (gui/)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Create venv if not exists
if [ ! -d "venv" ]; then
    echo "[launch.sh] Creating venv..."
    python3 -m venv venv
    echo "[launch.sh] Installing requirements..."
    ./venv/bin/pip install --upgrade pip
    ./venv/bin/pip install -r requirements.txt
else
    echo "[launch.sh] Using existing venv"
fi

# Ask username
read -rp "Enter username: " username

# Run app
echo "[launch.sh] Launching GUI app as user '$username'..."
./venv/bin/python main.py "$username"