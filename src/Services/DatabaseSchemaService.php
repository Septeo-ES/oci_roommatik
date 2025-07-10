<?php
// src/Services/DatabaseSchemaService.php
namespace App\Services;

use App\Models\Database;

class DatabaseSchemaService
{
    public static function ensureSchema()
    {
        $db = Database::getInstance()->getConnection();
        // Cargar el SQL del schema
        $schemaFile = __DIR__ . '/../../database/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new \Exception('No se encuentra el archivo schema.sql');
        }
        $sql = file_get_contents($schemaFile);
        // Ejecutar cada sentencia por separado
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if ($statement) {
                $db->exec($statement);
            }
        }
    }
}
