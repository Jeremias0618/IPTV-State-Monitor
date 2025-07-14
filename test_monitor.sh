#!/bin/bash

# Script de prueba para verificar la detección inmediata
# Ubicación: /var/www/html/IPTV/test_monitor.sh

echo "=== Test de Monitor de Canales IPTV ==="
echo "Fecha: $(date)"
echo ""

cd /var/www/html/IPTV

# Limpiar archivos de estado para simular primera ejecución
echo "🧹 Limpiando archivos de estado..."
rm -f estado_canales.json alertas_enviadas.json monitor_log.txt

echo ""
echo "📡 Ejecutando primera verificación..."
php canales_correo_mejorado.php > /dev/null 2>&1

echo "✅ Primera ejecución completada"
echo ""

# Mostrar contenido de archivos de estado
echo "📄 Archivo de estado actual:"
if [ -f estado_canales.json ]; then
    cat estado_canales.json | head -10
else
    echo "❌ No se creó el archivo de estado"
fi

echo ""
echo "📄 Archivo de alertas:"
if [ -f alertas_enviadas.json ]; then
    cat alertas_enviadas.json | head -10
else
    echo "❌ No se creó el archivo de alertas"
fi

echo ""
echo "📝 Log de ejecución:"
if [ -f monitor_log.txt ]; then
    cat monitor_log.txt
else
    echo "❌ No se creó el archivo de log"
fi

echo ""
echo "🎯 Test completado. Verifica los archivos generados." 