#!/bin/bash
# Automatic WhatsApp Reminder Cron Job
# Run this script every hour to send appointment reminders

# Change to script directory
cd "$(dirname "$0")"

# Run PHP script
/usr/bin/php send_reminders.php

# Exit with PHP script's exit code
exit $?
