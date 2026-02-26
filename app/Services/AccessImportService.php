<?php

namespace App\Services;

use RuntimeException;

class AccessImportService
{
    /**
     * Check which Access reading strategy is available.
     * Returns 'odbc', 'mdbtools', or null.
     */
    public static function availableDriver(): ?string
    {
        // Windows: PDO ODBC with Microsoft Access driver
        if (PHP_OS_FAMILY === 'Windows' && extension_loaded('pdo_odbc')) {
            return 'odbc';
        }

        // Linux: mdbtools CLI
        if (PHP_OS_FAMILY === 'Linux') {
            exec('which mdb-tables 2>/dev/null', $output, $code);
            if ($code === 0) {
                return 'mdbtools';
            }
        }

        return null;
    }

    /**
     * List user tables in an Access database.
     */
    public function listTables(string $filePath): array
    {
        $driver = static::availableDriver();

        if ($driver === 'odbc') {
            return $this->listTablesOdbc($filePath);
        }

        if ($driver === 'mdbtools') {
            return $this->listTablesMdbtools($filePath);
        }

        throw new RuntimeException(
            'No Access database driver available. On Windows, enable the pdo_odbc PHP extension. ' .
            'On Linux, install mdbtools (apt install mdbtools). ' .
            'Alternatively, export your Access tables to .xlsx and use the Excel import.'
        );
    }

    /**
     * Read all rows from a table as associative arrays.
     */
    public function readTable(string $filePath, string $tableName): array
    {
        $driver = static::availableDriver();

        if ($driver === 'odbc') {
            return $this->readTableOdbc($filePath, $tableName);
        }

        if ($driver === 'mdbtools') {
            return $this->readTableMdbtools($filePath, $tableName);
        }

        throw new RuntimeException('No Access database driver available.');
    }

    /**
     * Get column headers from a table.
     */
    public function getHeaders(string $filePath, string $tableName): array
    {
        $driver = static::availableDriver();

        if ($driver === 'odbc') {
            return $this->getHeadersOdbc($filePath, $tableName);
        }

        if ($driver === 'mdbtools') {
            $rows = $this->readTableMdbtools($filePath, $tableName);
            return ! empty($rows) ? array_keys($rows[0]) : [];
        }

        throw new RuntimeException('No Access database driver available.');
    }

    // ── ODBC (Windows) ──────────────────────────────────────────────

    private function connectOdbc(string $filePath): \PDO
    {
        $dsn = 'odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . $filePath;
        $pdo = new \PDO($dsn, '', '');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    private function listTablesOdbc(string $filePath): array
    {
        $conn = odbc_connect(
            'Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . $filePath,
            '', ''
        );

        $tables = [];
        $result = odbc_tables($conn);
        while ($row = odbc_fetch_array($result)) {
            if ($row['TABLE_TYPE'] === 'TABLE') {
                $tables[] = $row['TABLE_NAME'];
            }
        }
        odbc_close($conn);

        return $tables;
    }

    private function getHeadersOdbc(string $filePath, string $tableName): array
    {
        $pdo = $this->connectOdbc($filePath);
        $stmt = $pdo->query('SELECT TOP 1 * FROM [' . $tableName . ']');
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ? array_keys($row) : [];
    }

    private function readTableOdbc(string $filePath, string $tableName): array
    {
        $pdo = $this->connectOdbc($filePath);
        $stmt = $pdo->query('SELECT * FROM [' . $tableName . ']');

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── mdbtools (Linux) ────────────────────────────────────────────

    private function listTablesMdbtools(string $filePath): array
    {
        $escaped = escapeshellarg($filePath);
        $output = shell_exec("mdb-tables -1 {$escaped} 2>/dev/null");

        if ($output === null) {
            return [];
        }

        return array_filter(array_map('trim', explode("\n", $output)));
    }

    private function readTableMdbtools(string $filePath, string $tableName): array
    {
        $escapedFile = escapeshellarg($filePath);
        $escapedTable = escapeshellarg($tableName);

        // mdb-export outputs CSV
        $csv = shell_exec("mdb-export {$escapedFile} {$escapedTable} 2>/dev/null");

        if (empty($csv)) {
            return [];
        }

        $lines = array_filter(explode("\n", $csv));
        if (count($lines) < 2) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines));
        $rows = [];

        foreach ($lines as $line) {
            $values = str_getcsv($line);
            if (count($values) === count($headers)) {
                $rows[] = array_combine($headers, $values);
            }
        }

        return $rows;
    }
}
