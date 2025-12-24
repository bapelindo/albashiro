#!/bin/bash

# =====================================================
# Albashiro Reminder System - Manager (Linux)
# Manage the automatic WhatsApp reminder system via Crontab
# =====================================================

# Get absolute path to the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PHP_SCRIPT="$SCRIPT_DIR/send_reminders.php"
LOG_FILE="$SCRIPT_DIR/../../logs/cron_reminders.log"

# Function to pause
pause() {
    read -p "Press Enter to continue..."
}

# Function to show menu
show_menu() {
    clear
    echo "==========================================="
    echo "  ALBASHIRO REMINDER SYSTEM MANAGER (Linux)"
    echo "==========================================="
    echo ""
    echo " [1] INSTALL / ENABLE (Runs every 1 hour)"
    echo " [2] UNINSTALL / DISABLE"
    echo " [3] CHECK STATUS"
    echo " [4] RUN MANUALLY NOW (Test)"
    echo " [5] EXIT"
    echo ""
    read -p "Enter your choice (1-5): " CHOICE
}

# Install / Enable
install_cron() {
    clear
    echo "==========================================="
    echo "  INSTALLING REMINDER SYSTEM"
    echo "==========================================="
    echo ""
    
    # Check if PHP script exists
    if [ ! -f "$PHP_SCRIPT" ]; then
        echo "[ERROR] PHP script not found at: $PHP_SCRIPT"
        pause
        return
    fi

    # Create log directory if not exists
    mkdir -p "$(dirname "$LOG_FILE")"
    chmod 777 "$(dirname "$LOG_FILE")"

    # Define cron job command
    CRON_CMD="0 * * * * php $PHP_SCRIPT >> $LOG_FILE 2>&1"

    # Check if already installed
    crontab -l 2>/dev/null | grep -F "$PHP_SCRIPT" >/dev/null
    if [ $? -eq 0 ]; then
        echo "[INFO] Task is already installed."
    else
        # Append to crontab
        (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
        if [ $? -eq 0 ]; then
            echo "[SUCCESS] System installed successfully!"
            echo "Reminders will run automatically every hour."
        else
            echo "[ERROR] Failed to update crontab."
        fi
    fi
    echo ""
    pause
}

# Uninstall / Disable
uninstall_cron() {
    clear
    echo "==========================================="
    echo "  UNINSTALLING REMINDER SYSTEM"
    echo "==========================================="
    echo ""

    # Check if installed
    crontab -l 2>/dev/null | grep -F "$PHP_SCRIPT" >/dev/null
    if [ $? -ne 0 ]; then
        echo "[INFO] System is NOT installed."
    else
        # Remove from crontab
        crontab -l 2>/dev/null | grep -vF "$PHP_SCRIPT" | crontab -
        if [ $? -eq 0 ]; then
            echo "[SUCCESS] System uninstalled."
            echo "Automatic reminders are now STOPPED."
        else
            echo "[ERROR] Failed to update crontab."
        fi
    fi
    echo ""
    pause
}

# Check Status
check_status() {
    clear
    echo "==========================================="
    echo "  SYSTEM STATUS"
    echo "==========================================="
    echo ""

    crontab -l 2>/dev/null | grep -F "$PHP_SCRIPT" >/dev/null
    if [ $? -eq 0 ]; then
        echo "[INFO] System is INSTALLED and RUNNING."
        echo "Current Crontab Entry:"
        crontab -l | grep -F "$PHP_SCRIPT"
    else
        echo "[INFO] System is NOT INSTALLED."
    fi
    echo ""
    pause
}

# Run Manually
run_manual() {
    clear
    echo "==========================================="
    echo "  RUNNING MANUALLY (TEST)"
    echo "==========================================="
    echo ""
    echo "Running send_reminders.php..."
    echo ""
    php "$PHP_SCRIPT"
    echo ""
    echo "[DONE] Manual run completed."
    echo ""
    pause
}

# Main Loop
while true; do
    show_menu
    case $CHOICE in
        1) install_cron ;;
        2) uninstall_cron ;;
        3) check_status ;;
        4) run_manual ;;
        5) exit 0 ;;
        *) echo "Invalid option"; pause ;;
    esac
done
