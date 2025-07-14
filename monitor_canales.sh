#!/bin/bash

# Script para monitorear canales cada 2 segundos
# Ubicación: /var/www/html/IPTV/monitor_canales.sh

# Cambiar al directorio del proyecto
cd /var/www/html/IPTV

# Función para limpiar al salir
cleanup() {
    echo "Deteniendo monitor de canales..."
    exit 0
}

# Capturar señal de interrupción
trap cleanup SIGINT SIGTERM

echo "Iniciando monitor de canales - Presiona Ctrl+C para detener"
echo "Ejecutando cada 2 segundos..."
echo "Usando versión mejorada para detección inmediata de canales caídos"

# Bucle infinito
while true; do
    # Ejecutar el script PHP mejorado
    php canales_correo_mejorado.php > /dev/null 2>&1
    
    # Esperar 2 segundos
    sleep 2
done 