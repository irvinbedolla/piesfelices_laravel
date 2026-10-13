<?php
// Subir temporalmente a /sistema_v2/public/clear.php y visitar en el navegador
$target = __DIR__ . '/../storage/framework/views';
foreach (glob("$target/*") as $file) {
    if (is_file($file)) @unlink($file);
}
echo "Caché de vistas limpiada con éxito.";