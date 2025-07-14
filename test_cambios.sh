#!/bin/bash

# Script de prueba para verificar detección de cambios bidireccionales
# Ubicación: /var/www/html/IPTV/test_cambios.sh

echo "=== Test de Detección de Cambios Bidireccionales ==="
echo "Fecha: $(date)"
echo ""

cd /var/www/html/IPTV

# Función para mostrar el estado actual
mostrar_estado() {
    echo "📄 Estado actual del JSON:"
    if [ -f estado_canales.json ]; then
        cat estado_canales.json | head -5
    else
        echo "❌ No existe archivo de estado"
    fi
    echo ""
}

# Función para ejecutar y mostrar resultados
ejecutar_test() {
    echo "🔄 Ejecutando verificación..."
    php canales_correo_mejorado.php > /dev/null 2>&1
    
    echo "✅ Ejecución completada"
    echo "📝 Log de la ejecución:"
    if [ -f monitor_log.txt ]; then
        tail -1 monitor_log.txt
    fi
    echo ""
}

# Limpiar archivos para empezar limpio
echo "🧹 Limpiando archivos de estado..."
rm -f estado_canales.json monitor_log.txt

echo ""
echo "🎯 PRUEBA 1: Primera ejecución (debería detectar canales offline)"
ejecutar_test
mostrar_estado

echo "🎯 PRUEBA 2: Segunda ejecución (sin cambios)"
ejecutar_test
mostrar_estado

echo "🎯 PRUEBA 3: Tercera ejecución (sin cambios)"
ejecutar_test
mostrar_estado

echo "📊 RESUMEN DE PRUEBAS:"
echo "✅ Se ejecutaron 3 verificaciones"
echo "📄 Archivo JSON creado: $(ls -la estado_canales.json 2>/dev/null | awk '{print $5}' || echo 'No existe')"
echo "📝 Log generado: $(ls -la monitor_log.txt 2>/dev/null | awk '{print $5}' || echo 'No existe')"

echo ""
echo "🔍 Para verificar cambios en tiempo real:"
echo "   tail -f monitor_log.txt"
echo ""
echo "🌐 Para ver la interfaz web:"
echo "   http://tu-servidor-ip/IPTV/canales_correo_mejorado.php" 