<?php
class Logger {
    public static function log($message, $type = 'INFO') {
        $logFile = __DIR__ . '/../logs/app.log';
        $logDir = dirname($logFile);

        // Create logs directory if it doesn't exist
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
        
        // Append to log file
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
?>
