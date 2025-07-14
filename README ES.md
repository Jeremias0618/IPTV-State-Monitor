# 📡 Monitor de Canales IPTV

Sistema de monitoreo automático para canales IPTV que detecta cambios de estado (ONLINE/OFFLINE) y envía alertas por correo electrónico.

## 🚀 Características

- ✅ Monitoreo automático cada 2 segundos
- 📧 Alertas por correo electrónico **inmediatas**
- 📊 Estadísticas en tiempo real (por consola)
- 🔄 Detección de cambios de estado **bidireccional** (ONLINE ↔ OFFLINE)
- 🛡️ Servicio systemd para ejecución automática
- 📝 Logs del sistema y debugging
- 🚨 Detección inmediata de canales caídos en primera ejecución
- ✅ Notificación cuando canales se restablecen (ONLINE)

## 📋 Requisitos del Sistema

- Ubuntu 20.04 LTS
- PHP 7.4 o superior
- MySQL/MariaDB
- Composer (para dependencias PHP)
- Permisos de root para instalar el servicio

## 📁 Estructura del Proyecto

```
/var/www/html/IPTV/
├── canales_correo_mejorado.php     # Script principal de monitoreo (versión recomendada)
├── monitor_canales.sh              # Script de ejecución automática
├── test_monitor.sh                 # Script de prueba básico
├── test_cambios.sh                 # Script de prueba bidireccional
├── iptv-monitor.service            # Configuración del servicio systemd
├── instalar_monitor.sh             # Script de instalación
├── composer.json                   # Dependencias PHP
├── composer.lock                   # Dependencias PHP (lock)
├── vendor/                         # Librerías PHP (PHPMailer)
├── README.md                       # Documentación en inglés
├── README ES.md                    # Documentación en español
└── (archivos generados: estado_canales.json, alertas_enviadas.json, monitor_log.txt)
```

## 🔧 Instalación

### Paso 1: Preparar el entorno

```bash
# Actualiza el sistema operativo y los paquetes
sudo apt update && sudo apt upgrade -y

# Instala PHP y las extensiones necesarias para el monitoreo y envío de correos
sudo apt install php php-mysql php-curl php-json php-mbstring -y

# Descarga e instala Composer (gestor de dependencias PHP)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Paso 2: Configurar la base de datos y correo

Abre el archivo `canales_correo_mejorado.php` y edita las siguientes líneas con tus datos reales:

```php
// Configuración de la base de datos
$host = 'your-db-host';      // Dirección/IP del servidor de base de datos
$port = 'your-db-port';      // Puerto de la base de datos (por defecto MySQL: 3306)
$dbname = 'your-db-name';    // Nombre de la base de datos
$username = 'your-db-user';  // Usuario de la base de datos
$password = 'your-db-password'; // Contraseña de la base de datos

// Configuración del correo
$correo_origen     = 'your-email@example.com';         // Correo remitente (origen)
$correo_destino    = 'destination-email@example.com';  // Correo destinatario (alertas)
$nombre_remitente  = '📡 Nombre de tu sistema';        // Nombre que aparecerá como remitente
$token_aplicacion  = 'tu-app-token';                  // Token de aplicación (Gmail)
```

### Paso 3: Instalar dependencias PHP

```bash
# Entra al directorio del proyecto
cd /var/www/html/IPTV/

# Instala las dependencias PHP (PHPMailer y otras)
composer install
```

### Paso 4: Configurar permisos

```bash
# Asigna el usuario y grupo www-data al directorio (recomendado para servidores web)
sudo chown -R www-data:www-data /var/www/html/IPTV/

# Da permisos de ejecución al script principal de monitoreo
sudo chmod +x /var/www/html/IPTV/monitor_canales.sh
```

### Paso 5: Instalar el servicio

```bash
# Ejecuta el script de instalación para registrar el servicio systemd
sudo bash /var/www/html/IPTV/instalar_monitor.sh
```

## 🚀 Ejecución

### Método 1: Servicio Automático (Recomendado)

```bash
# Inicia el servicio de monitoreo
sudo systemctl start iptv-monitor

# Verifica el estado del servicio
sudo systemctl status iptv-monitor

# Habilita el servicio para que inicie automáticamente con el sistema
sudo systemctl enable iptv-monitor
```

### Método 2: Ejecución Manual

```bash
# Ejecuta el monitoreo manualmente en consola
cd /var/www/html/IPTV/
./monitor_canales.sh
```

### Método 3: Ejecutar una sola vez

```bash
# Ejecuta el script PHP de monitoreo una sola vez (útil para pruebas)
cd /var/www/html/IPTV/
php canales_correo_mejorado.php
```

### Método 4: Pruebas

```bash
# Prueba la detección inmediata de canales caídos
chmod +x test_monitor.sh
./test_monitor.sh

# Prueba la detección bidireccional (ONLINE ↔ OFFLINE)
chmod +x test_cambios.sh
./test_cambios.sh
```

## 📊 Monitoreo y Logs

```bash
# Ver logs del servicio en tiempo real
sudo journalctl -u iptv-monitor -f

# Ver las últimas 100 líneas del log
sudo journalctl -u iptv-monitor -n 100

# Verifica el estado del servicio
sudo systemctl status iptv-monitor
```

## 🔧 Configuración

- **Correo electrónico:** Edita las variables en `canales_correo_mejorado.php` para definir remitente, destinatario y token.
- **Intervalo de monitoreo:** Edita el valor de `sleep` en `monitor_canales.sh` (por defecto: 2 segundos) para ajustar la frecuencia de verificación.

## 🛠️ Comandos de Gestión

```bash
# Iniciar el servicio de monitoreo
sudo systemctl start iptv-monitor

# Detener el servicio
sudo systemctl stop iptv-monitor

# Reiniciar el servicio
sudo systemctl restart iptv-monitor

# Ver el estado del servicio
sudo systemctl status iptv-monitor

# Habilitar inicio automático
sudo systemctl enable iptv-monitor

# Deshabilitar inicio automático
sudo systemctl disable iptv-monitor

# Ver logs en tiempo real
sudo journalctl -u iptv-monitor -f
```

## 🔍 Solución de Problemas

### El servicio no inicia

```bash
# Verifica los logs de systemd para encontrar errores
sudo journalctl -u iptv-monitor -n 50

# Verifica los permisos del script de monitoreo
ls -la /var/www/html/IPTV/monitor_canales.sh

# Verifica que PHP esté instalado correctamente
php --version
```

### Error de conexión a la base de datos

```bash
# Prueba la conexión manualmente a la base de datos
mysql -h your-db-host -P your-db-port -u your-db-user -p your-db-name
# ejemplo: mysql -h 192.168.18.1 -P 3306 -u user_iptv -p xtream_iptv

# Verifica que el puerto esté abierto
# (útil para diagnosticar problemas de red/firewall)
telnet your-db-host your-db-port
# ejemplo: telnet 192.168.18.1 3306
```

### Error de permisos

```bash
# Corrige los permisos del directorio y scripts
sudo chown -R www-data:www-data /var/www/html/IPTV/
sudo chmod +x /var/www/html/IPTV/monitor_canales.sh
```

### El servicio se detiene automáticamente

```bash
# Verifica los logs para identificar el error
sudo journalctl -u iptv-monitor -f

# Verifica que el archivo PHP no tenga errores de sintaxis
php -l /var/www/html/IPTV/canales_correo_mejorado.php
```

## 📧 Configuración de Gmail

1. Activa la verificación en dos pasos en tu cuenta de Gmail
2. Genera una contraseña de aplicación
3. Usa esa contraseña en la variable `$token_aplicacion` en el script PHP

## 🔒 Seguridad

- El script se ejecuta con el usuario `www-data`.
- Los logs se guardan en el sistema de logs de Ubuntu.
- El servicio se reinicia automáticamente si falla.
- Los archivos de estado se guardan con permisos apropiados.

## 📞 Soporte

1. Revisa los logs: `sudo journalctl -u iptv-monitor -f`
2. Verifica la conexión a la base de datos
3. Comprueba que PHP y las extensiones estén instaladas
4. Verifica los permisos de los archivos

## 📝 Notas

- El archivo `estado_canales.json` se crea automáticamente
- Los correos se envían **inmediatamente** cuando hay cambios de estado
- El servicio se reinicia automáticamente si falla
- Los logs se rotan automáticamente por systemd
- **Detección bidireccional**: Detecta tanto canales que caen (OFFLINE) como que se levantan (ONLINE)
- Timeout de SMTP reducido a 10 segundos para respuesta más rápida
- **Cada 2 segundos**: Se consulta la base de datos y se compara con el JSON anterior
- **Actualización automática**: El archivo JSON se actualiza en cada ejecución

---

**Desarrollado para CyberCode Labs** 📡 
