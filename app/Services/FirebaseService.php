<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseService
{
    protected Database $database;

    public function __construct()
    {
        $factory = new Factory();
        
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS_PATH'));
        
        if (file_exists($credentialsPath)) {
            $firebase = $factory->withServiceAccount($credentialsPath);
        } else {
            // Fallback a configuración sin archivo de credenciales
            $firebase = $factory;
        }
        
        $this->database = $firebase->withDatabaseUri(env('FIREBASE_DATABASE_URL'))->createDatabase();
    }

    /**
     * Guardar una adopción en Firebase
     */
    public function saveAdoption(array $data)
    {
        try {
            $reference = $this->database->getReference('adopciones');
            $reference->push($data);
            
            return [
                'success' => true,
                'message' => 'Adopción registrada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al guardar adopción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener todas las adopciones
     */
    public function getAllAdoptions()
    {
        try {
            $reference = $this->database->getReference('adopciones');
            $snapshot = $reference->getSnapshot();
            
            return $snapshot->exists() ? $snapshot->getValue() : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener una adopción por ID
     */
    public function getAdoptionById(string $id)
    {
        try {
            $reference = $this->database->getReference('adopciones/' . $id);
            $snapshot = $reference->getSnapshot();
            
            return $snapshot->exists() ? $snapshot->getValue() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Actualizar una adopción
     */
    public function updateAdoption(string $id, array $data)
    {
        try {
            $reference = $this->database->getReference('adopciones/' . $id);
            $reference->update($data);
            
            return [
                'success' => true,
                'message' => 'Adopción actualizada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar adopción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar una adopción
     */
    public function deleteAdoption(string $id)
    {
        try {
            $reference = $this->database->getReference('adopciones/' . $id);
            $reference->remove();
            
            return [
                'success' => true,
                'message' => 'Adopción eliminada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar adopción: ' . $e->getMessage()
            ];
        }
    }
}
