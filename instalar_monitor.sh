#!/bin/bash

# Script de instalación para el monitor de canales IPTV
# Ejecutar como root: sudo bash instalar_monitor.sh

echo "=== Instalador del Monitor de Canales IPTV ==="

# Verificar si se ejecuta como root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Este script debe ejecutarse como root (sudo)"
    exit 1
fi

# Directorio del proyecto
PROJECT_DIR="/var/www/html/IPTV"

# Verificar que el directorio existe
if [ ! -d "$PROJECT_DIR" ]; then
    echo "❌ El directorio $PROJECT_DIR no existe"
    exit 1
fi

echo "📁 Directorio del proyecto: $PROJECT_DIR"

# Dar permisos de ejecución al script monitor
chmod +x "$PROJECT_DIR/monitor_canales.sh"

# Copiar el servicio systemd
cp "$PROJECT_DIR/iptv-monitor.service" /etc/systemd/system/

# Recargar systemd
systemctl daemon-reload

# Habilitar el servicio para que inicie con el sistema
systemctl enable iptv-monitor.service

echo "✅ Instalación completada"
echo ""
echo "📋 Comandos útiles:"
echo "   Iniciar servicio: sudo systemctl start iptv-monitor"
echo "   Detener servicio: sudo systemctl stop iptv-monitor"
echo "   Ver estado: sudo systemctl status iptv-monitor"
echo "   Ver logs: sudo journalctl -u iptv-monitor -f"
echo ""
echo "🚀 El servicio se iniciará automáticamente al reiniciar el servidor" 