# 📡 IPTV Channel Monitor

Automatic monitoring system for IPTV channels that detects status changes (ONLINE/OFFLINE) and sends email alerts.

## 🚀 Features

- ✅ Automatic monitoring every 2 seconds
- 📧 **Immediate** email alerts
- 📊 Real-time statistics (console output)
- 🔄 **Bidirectional** status change detection (ONLINE ↔ OFFLINE)
- 🛡️ systemd service for automatic execution
- 📝 System logs and debugging
- 🚨 Immediate detection of down channels on first run
- ✅ Notification when channels are restored (ONLINE)

## 📋 System Requirements

- Ubuntu 20.04 LTS
- PHP 7.4 or higher
- MySQL/MariaDB
- Composer (for PHP dependencies)
- Root permissions to install the service

## 📁 Project Structure

```
/var/www/html/IPTV/
├── canales_correo_mejorado.php     # Main monitoring script (recommended version)
├── monitor_canales.sh              # Automatic execution script
├── test_monitor.sh                 # Basic test script
├── test_cambios.sh                 # Bidirectional test script
├── iptv-monitor.service            # systemd service configuration
├── instalar_monitor.sh             # Installation script
├── composer.json                   # PHP dependencies
├── composer.lock                   # PHP dependencies (lock)
├── vendor/                         # PHP libraries (PHPMailer)
├── README.md                       # Documentation in English
├── README ES.md                    # Documentation in Spanish
└── (generated files: estado_canales.json, alertas_enviadas.json, monitor_log.txt)
```

## 🔧 Installation

### Step 1: Prepare the environment

```bash
# Update the operating system and packages
sudo apt update && sudo apt upgrade -y

# Install PHP and the required extensions for monitoring and sending emails
sudo apt install php php-mysql php-curl php-json php-mbstring -y

# Download and install Composer (PHP dependency manager)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Step 2: Configure the database and email

Open the file `canales_correo_mejorado.php` and edit the following lines with your real data:

```php
// Database configuration
$host = 'your-db-host';      // Database server address/IP
$port = 'your-db-port';      // Database port (default MySQL: 3306)
$dbname = 'your-db-name';    // Database name
$username = 'your-db-user';  // Database user
$password = 'your-db-password'; // Database password

// Email configuration
$correo_origen     = 'your-email@example.com';         // Sender email (origin)
$correo_destino    = 'destination-email@example.com';  // Recipient email (alerts)
$nombre_remitente  = '📡 Your system name';            // Name that will appear as sender
$token_aplicacion  = 'your-app-token';                // Application token (Gmail)
```

> **Note:** Never upload your real data to a public repository.

### Step 3: Install PHP dependencies

```bash
# Enter the project directory
cd /var/www/html/IPTV/

# Install PHP dependencies (PHPMailer and others)
composer install
```

### Step 4: Set permissions

```bash
# Assign the www-data user and group to the directory (recommended for web servers)
sudo chown -R www-data:www-data /var/www/html/IPTV/

# Give execution permissions to the main monitoring script
sudo chmod +x /var/www/html/IPTV/monitor_canales.sh
```

### Step 5: Install the service

```bash
# Run the installation script to register the systemd service
sudo bash /var/www/html/IPTV/instalar_monitor.sh
```

## 🚀 Execution

### Method 1: Automatic Service (Recommended)

```bash
# Start the monitoring service
sudo systemctl start iptv-monitor

# Check the service status
sudo systemctl status iptv-monitor

# Enable the service to start automatically with the system
sudo systemctl enable iptv-monitor
```

### Method 2: Manual Execution

```bash
# Run monitoring manually in the console
cd /var/www/html/IPTV/
./monitor_canales.sh
```

### Method 3: Run once

```bash
# Run the PHP monitoring script once (useful for testing)
cd /var/www/html/IPTV/
php canales_correo_mejorado.php
```

### Method 4: Tests

```bash
# Test immediate detection of down channels
chmod +x test_monitor.sh
./test_monitor.sh

# Test bidirectional detection (ONLINE ↔ OFFLINE)
chmod +x test_cambios.sh
./test_cambios.sh
```

## 📊 Monitoring and Logs

```bash
# View service logs in real time
sudo journalctl -u iptv-monitor -f

# View the last 100 lines of the log
sudo journalctl -u iptv-monitor -n 100

# Check the service status
sudo systemctl status iptv-monitor
```

## 🔧 Configuration

- **Email:** Edit the variables in `canales_correo_mejorado.php` to set sender, recipient, and token.
- **Monitoring interval:** Edit the `sleep` value in `monitor_canales.sh` (default: 2 seconds) to adjust the check frequency.

## 🛠️ Management Commands

```bash
# Start the monitoring service
sudo systemctl start iptv-monitor

# Stop the service
sudo systemctl stop iptv-monitor

# Restart the service
sudo systemctl restart iptv-monitor

# View the service status
sudo systemctl status iptv-monitor

# Enable automatic start
sudo systemctl enable iptv-monitor

# Disable automatic start
sudo systemctl disable iptv-monitor

# View logs in real time
sudo journalctl -u iptv-monitor -f
```

## 🔍 Troubleshooting

### The service does not start

```bash
# Check systemd logs for errors
sudo journalctl -u iptv-monitor -n 50

# Check permissions of the monitoring script
ls -la /var/www/html/IPTV/monitor_canales.sh

# Check that PHP is installed correctly
php --version
```

### Database connection error

```bash
# Test the connection to the database manually
mysql -h your-db-host -P your-db-port -u your-db-user -p your-db-name
# example: mysql -h 192.168.18.1 -P 3306 -u user_iptv -p xtream_iptv

# Check that the port is open
# (useful for diagnosing network/firewall issues)
telnet your-db-host your-db-port
# example: telnet 192.168.18.1 3306
```

### Permission error

```bash
# Fix directory and script permissions
sudo chown -R www-data:www-data /var/www/html/IPTV/
sudo chmod +x /var/www/html/IPTV/monitor_canales.sh
```

### The service stops automatically

```bash
# Check logs to identify the error
sudo journalctl -u iptv-monitor -f

# Check that the PHP file has no syntax errors
php -l /var/www/html/IPTV/canales_correo_mejorado.php
```

## 📧 Gmail Configuration

1. Enable two-step verification on your Gmail account
2. Generate an application password
3. Use that password in the `$token_aplicacion` variable in the PHP script

## 🔒 Security

- The script runs as the `www-data` user.
- Logs are saved in Ubuntu's log system.
- The service restarts automatically if it fails.
- Status files are saved with appropriate permissions.

## 📞 Support

1. Check the logs: `sudo journalctl -u iptv-monitor -f`
2. Verify the database connection
3. Make sure PHP and extensions are installed
4. Check file permissions

## 📝 Notes

- The file `estado_canales.json` is created automatically
- Emails are sent **immediately** when there are status changes
- The service restarts automatically if it fails
- Logs are rotated automatically by systemd
- **Bidirectional detection**: Detects both channels that go down (OFFLINE) and those that come back up (ONLINE)
- SMTP timeout reduced to 10 seconds for faster response
- **Every 2 seconds**: The database is queried and compared with the previous JSON
- **Automatic update**: The JSON file is updated on each run

---

**Developed for CyberCode Labs** 📡 
