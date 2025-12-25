<?php

/**
 * ServerTiming Helper Class
 * Handles collection and output of Server-Timing headers
 */
class ServerTiming
{
    private static $timings = [];
    private static $metrics = [];

    /**
     * Start a timer
     * @param string $name
     */
    public static function start($name)
    {
        self::$timings[$name] = microtime(true);
    }

    /**
     * Stop a timer and add to metrics
     * @param string $name
     * @param string $description Optional description
     */
    public static function stop($name, $description = '')
    {
        if (isset(self::$timings[$name])) {
            $duration = (microtime(true) - self::$timings[$name]) * 1000; // ms
            self::accumulate($name, $duration, $description);
            unset(self::$timings[$name]);
        }
    }

    /**
     * Add a duration directly to metrics
     * @param string $name
     * @param float $duration in milliseconds
     * @param string $description Optional description
     */
    public static function accumulate($name, $duration, $description = '')
    {
        if (!isset(self::$metrics[$name])) {
            self::$metrics[$name] = [
                'duration' => 0,
                'count' => 0,
                'desc' => $description
            ];
        }
        self::$metrics[$name]['duration'] += $duration;
        self::$metrics[$name]['count']++;
    }

    /**
     * Send the Server-Timing header
     */
    public static function sendHeaders()
    {
        if (empty(self::$metrics)) {
            return;
        }

        $parts = [];
        foreach (self::$metrics as $name => $data) {
            // Format: name;dur=123.4;desc="description"
            $part = sprintf('%s;dur=%.2f', $name, $data['duration']);
            if (!empty($data['desc'])) {
                $part .= sprintf(';desc="%s"', addslashes($data['desc']));
            }
            $parts[] = $part;
        }

        header('Server-Timing: ' . implode(', ', $parts));
    }
}
