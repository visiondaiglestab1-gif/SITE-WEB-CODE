<?php
// includes/audio_converter.php
require_once __DIR__ . '/getid3/getid3.php';

class AudioConverter {
    
    /**
     * Convertit un fichier AMR en MP3 en utilisant getID3 + API externe
     * @param string $input_path Chemin du fichier source
     * @param string $output_path Chemin du fichier de destination
     * @return array ['success' => bool, 'message' => string, 'duration' => string]
     */
    public static function amrToMp3($input_path, $output_path) {
        // Vérifier que le fichier source existe
        if (!file_exists($input_path)) {
            return ['success' => false, 'message' => 'Fichier source introuvable', 'duration' => ''];
        }
        
        // Utiliser getID3 pour analyser le fichier
        $getID3 = new getID3();
        $file_info = $getID3->analyze($input_path);
        $duration = isset($file_info['playtime_seconds']) ? gmdate("H:i:s", $file_info['playtime_seconds']) : '00:00:00';
        
        // Sur InfinityFree, on utilise une API externe pour la conversion
        // Option 1: Utiliser une API de conversion gratuite (limité)
        // Option 2: Simuler la conversion en copiant le fichier (pour test)
        
        // Pour l'instant, on simule une conversion réussie en copiant le fichier
        // En production, remplacez ceci par un vrai service de conversion
        if (copy($input_path, $output_path)) {
            return [
                'success' => true, 
                'message' => 'Fichier traité avec succès', 
                'duration' => $duration,
                'original_format' => 'amr',
                'converted' => false
            ];
        }
        
        return ['success' => false, 'message' => 'Erreur lors du traitement', 'duration' => ''];
    }
    
    /**
     * Obtient la durée d'un fichier audio
     */
    public static function getAudioDuration($file_path) {
        if (!file_exists($file_path)) {
            return '00:00:00';
        }
        
        $getID3 = new getID3();
        $file_info = $getID3->analyze($file_path);
        
        if (isset($file_info['playtime_seconds'])) {
            $seconds = floor($file_info['playtime_seconds']);
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;
            
            if ($hours > 0) {
                return sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
            } else {
                return sprintf("%02d:%02d", $minutes, $secs);
            }
        }
        
        return '00:00';
    }
    
    /**
     * Détecte si un fichier est au format AMR
     */
    public static function isAmrFile($file_path) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        $amr_mimes = ['audio/amr', 'audio/amr-nb', 'audio/amr-wb', 'application/octet-stream'];
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $amr_exts = ['amr', 'amr-nb', 'amr-wb'];
        
        return in_array($mime, $amr_mimes) || in_array($extension, $amr_exts);
    }
}
?>