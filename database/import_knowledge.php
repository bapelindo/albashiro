<?php
/**
 * CSV to SQL Importer for AI Knowledge Base
 * Import multiple CSV files into ai_knowledge_base table
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

class KnowledgeImporter
{
    private $db;
    private $imported = 0;
    private $errors = 0;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Import all CSV files from knowledge_csv directory
     */
    public function importAll()
    {
        $csvDir = __DIR__ . '/knowledge_csv/';

        if (!is_dir($csvDir)) {
            die("Error: knowledge_csv directory not found!\n");
        }

        $csvFiles = glob($csvDir . '*.csv');

        if (empty($csvFiles)) {
            die("Error: No CSV files found in knowledge_csv directory!\n");
        }

        echo "Found " . count($csvFiles) . " CSV files to import.\n\n";

        foreach ($csvFiles as $file) {
            $this->importFile($file);
        }

        echo "\n=== IMPORT COMPLETE ===\n";
        echo "Total imported: {$this->imported}\n";
        echo "Total errors: {$this->errors}\n";
    }

    /**
     * Import single CSV file
     */
    private function importFile($filepath)
    {
        $filename = basename($filepath);
        echo "Importing: $filename ... ";

        $handle = fopen($filepath, 'r');
        if (!$handle) {
            echo "ERROR: Cannot open file!\n";
            $this->errors++;
            return;
        }

        // Skip header row
        $header = fgetcsv($handle, 0, ',', '"', '\\');

        $count = 0;

        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== FALSE) {
            if (count($data) < 6)
                continue; // Skip invalid rows

            try {
                $this->db->query("
                    INSERT INTO ai_knowledge_base 
                    (category, topic, question, answer, keywords, priority, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)
                ", [
                    $data[0], // category
                    $data[1], // topic
                    $data[2], // question
                    $data[3], // answer
                    $data[4], // keywords
                    (int) $data[5] // priority
                ]);
                $count++;
                $this->imported++;
            } catch (Exception $e) {
                $this->errors++;
                echo "\nError on row: " . implode(',', $data) . "\n";
                echo "Message: " . $e->getMessage() . "\n";
            }
        }

        fclose($handle);
        echo "OK ($count entries)\n";
    }

    /**
     * Clear existing knowledge base
     */
    public function clearAll()
    {
        echo "Clearing existing knowledge base... ";
        $this->db->query("TRUNCATE TABLE ai_knowledge_base");
        echo "OK\n\n";
    }
}

// Run importer
echo "=== AI KNOWLEDGE BASE IMPORTER ===\n\n";

$importer = new KnowledgeImporter();

// Ask if user wants to clear existing data
echo "Clear existing data? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) == 'y') {
    $importer->clearAll();
}
fclose($handle);

// Import all CSV files
$importer->importAll();

echo "\nDone!\n";
