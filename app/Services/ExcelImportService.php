<?php

namespace App\Services;

use App\Models\Child;
use App\Models\Family;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportService
{
    // Mapping of normalized column names to database columns.
    // Normalization strips spaces, underscores, dashes, question marks,
    // parenthetical text, and lowercases everything.
    private const FAMILY_COLUMNS = [
        'familynumber' => 'family_number',
        'familyname' => 'family_name',
        'nameoffamily' => 'family_name',
        'address' => 'address',
        'physicaladdress' => 'address',
        'phone1' => 'phone1',
        'phone' => 'phone1',
        'phonenumber' => 'phone1',
        'phone2' => 'phone2',
        'altphonenumber' => 'phone2',
        'email' => 'email',
        'preferredlanguage' => 'preferred_language',
        'femaleadults' => 'female_adults',
        'maleadults' => 'male_adults',
        'otheradults' => 'other_adults',
        'numberofadults' => 'number_of_adults',
        'infants' => 'infants',
        'youngchildren' => 'young_children',
        'youngchild' => 'young_children',
        'child' => 'children_count',
        'childrencount' => 'children_count',
        'tweens' => 'tweens',
        'tween' => 'tweens',
        'teenagers' => 'teenagers',
        'teenager' => 'teenagers',
        'numberofchildren' => 'number_of_children',
        'numberoffamilymembers' => 'number_of_family_members',
        'numberofboxes' => 'number_of_boxes',
        'numberoffoodboxes' => 'number_of_boxes',
        'petinformation' => 'pet_information',
        'whatpetsdoesfamilyhave' => 'pet_information',
        'deliverypreference' => 'delivery_preference',
        'deliveryreason' => 'delivery_reason',
        'iffamilycannothaveitemsdeliveredwhy' => 'delivery_reason',
        'deliveryteam' => 'delivery_team',
        'deliverydate' => 'delivery_date',
        'deliverytime' => 'delivery_time',
        'deliverystatus' => 'delivery_status',
        'needforhelp' => 'need_for_help',
        'whyfamilyneedshelp' => 'need_for_help',
        'severeneed' => 'severe_need',
        'otherquestions' => 'other_questions',
        'dotheyhaveanyquestions' => 'other_questions',
        'hascrhschildren' => 'has_crhs_children',
        'anychildrenwhoattendcrhs' => 'has_crhs_children',
        'hasgfhschildren' => 'has_gfhs_children',
        'anychildrenwhoattendgfhs' => 'has_gfhs_children',
        'needsbabysupplies' => 'needs_baby_supplies',
        'doesfamilyneedbabysuppliesfood' => 'needs_baby_supplies',
    ];

    private const CHILD_COLUMNS = [
        'familynumber' => '_family_number',
        'familyid' => '_family_id',
        'gender' => 'gender',
        'age' => 'age',
        'school' => 'school',
        'clothessize' => 'clothes_size',
        'clothingstyles' => 'clothing_styles',
        'clothingoptions' => 'clothing_options',
        'giftpreferences' => 'gift_preferences',
        'toyideas' => 'toy_ideas',
        'allsizes' => 'all_sizes',
        'mailmerged' => 'mail_merged',
        'mailmerge' => 'mail_merged',
        'giftlevel' => 'gift_level',
        'whereistag' => 'where_is_tag',
        'adoptername' => 'adopter_name',
        'adoptersname' => 'adopter_name',
        'adoptercontactinfo' => 'adopter_contact_info',
        'adopterscontactinfo' => 'adopter_contact_info',
        'giftsreceived' => 'gifts_received',
    ];

    /**
     * Normalize a column header for matching.
     * Strips parenthetical text, question marks, spaces, underscores, dashes.
     */
    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/\([^)]*\)/', '', $header); // remove (parenthetical)
        $header = preg_replace('/[?\s_\-\'",\/]+/', '', $header); // remove ?, spaces, _, -, quotes, commas, slashes
        return strtolower(trim($header));
    }

    /**
     * Get the column map for a type.
     */
    public function getColumnMap(string $type): array
    {
        return $type === 'family' ? self::FAMILY_COLUMNS : self::CHILD_COLUMNS;
    }

    /**
     * Build a mapped preview from header names.
     */
    public function mapHeaders(array $headers, string $type): array
    {
        $columnMap = $this->getColumnMap($type);
        $mapped = [];
        foreach ($headers as $key => $header) {
            if ($header === null || $header === '') continue;
            $normalized = $this->normalizeHeader($header);
            $dbColumn = $columnMap[$normalized] ?? null;
            $mapped[$key] = [
                'original' => $header,
                'mapped_to' => $dbColumn,
            ];
        }
        return $mapped;
    }

    /**
     * Parse an Excel file and return headers and preview data.
     */
    public function preview(string $filePath, string $type): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return ['headers' => [], 'mapped' => [], 'preview' => []];
        }

        $headers = array_shift($rows);
        $mapped = $this->mapHeaders($headers, $type);
        $preview = array_slice(array_values($rows), 0, 5);

        return [
            'headers' => $headers,
            'mapped' => $mapped,
            'preview' => $preview,
        ];
    }

    /**
     * Preview from raw associative arrays (for Access import).
     */
    public function previewFromRows(array $rows, string $type): array
    {
        if (empty($rows)) {
            return ['headers' => [], 'mapped' => [], 'preview' => []];
        }

        $headers = array_keys($rows[0]);
        $mapped = $this->mapHeaders($headers, $type);
        $preview = array_slice($rows, 0, 5);

        return [
            'headers' => $headers,
            'mapped' => $mapped,
            'preview' => $preview,
        ];
    }

    /**
     * Import families from Excel file.
     */
    public function importFamilies(string $filePath, int $seasonYear): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => []];
        }

        $headers = array_shift($rows);
        $columnMapping = $this->buildColumnMapping($headers, self::FAMILY_COLUMNS);

        return $this->importFamilyRows(
            array_values($rows),
            $columnMapping,
            $seasonYear,
            'indexed'
        );
    }

    /**
     * Import children from Excel file.
     */
    public function importChildren(string $filePath, int $seasonYear): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => []];
        }

        $headers = array_shift($rows);
        $columnMapping = $this->buildColumnMapping($headers, self::CHILD_COLUMNS);

        return $this->importChildRows(
            array_values($rows),
            $columnMapping,
            $seasonYear,
            'indexed'
        );
    }

    /**
     * Import families from raw associative arrays (Access DB rows).
     */
    public function importFamiliesFromRows(array $rows, int $seasonYear): array
    {
        if (empty($rows)) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => []];
        }

        $headers = array_keys($rows[0]);
        $columnMapping = $this->buildColumnMapping($headers, self::FAMILY_COLUMNS);

        return $this->importFamilyRows($rows, $columnMapping, $seasonYear, 'assoc');
    }

    /**
     * Import children from raw associative arrays (Access DB rows).
     */
    public function importChildrenFromRows(array $rows, int $seasonYear, ?array $familyIdMap = null): array
    {
        if (empty($rows)) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => []];
        }

        $headers = array_keys($rows[0]);
        $columnMapping = $this->buildColumnMapping($headers, self::CHILD_COLUMNS);

        return $this->importChildRows($rows, $columnMapping, $seasonYear, 'assoc', $familyIdMap);
    }

    // ── Internal import logic ───────────────────────────────────────

    private function importFamilyRows(array $rows, array $columnMapping, int $seasonYear, string $mode): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $rowNum => $row) {
            try {
                $data = ($mode === 'assoc')
                    ? $this->mapAssocRow($row, $columnMapping)
                    : $this->mapRow($row, $columnMapping);

                if (empty($data['family_name']) && empty($data['family_number'])) {
                    $skipped++;
                    continue;
                }

                $data['season_year'] = $seasonYear;

                // Cast numeric fields
                foreach (['family_number', 'female_adults', 'male_adults', 'other_adults', 'number_of_adults', 'infants', 'young_children', 'children_count', 'tweens', 'teenagers', 'number_of_children', 'number_of_family_members', 'number_of_boxes'] as $field) {
                    if (isset($data[$field])) {
                        $data[$field] = (int) $data[$field];
                    }
                }

                // Cast boolean fields from YES/NO strings
                foreach (['has_crhs_children', 'has_gfhs_children', 'needs_baby_supplies', 'severe_need'] as $field) {
                    if (isset($data[$field])) {
                        $data[$field] = $this->castBool($data[$field]);
                    }
                }

                Family::withoutGlobalScopes()->create($data);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($rowNum + 2) . ": " . $e->getMessage();
            }
        }

        return compact('imported', 'skipped', 'errors');
    }

    private function importChildRows(array $rows, array $columnMapping, int $seasonYear, string $mode, ?array $familyIdMap = null): array
    {
        // Build family lookup: family_number → id for this season
        $familyByNumber = Family::withoutGlobalScopes()
            ->where('season_year', $seasonYear)
            ->whereNotNull('family_number')
            ->pluck('id', 'family_number')
            ->toArray();

        // Also build family_id (Access internal ID) → our family_id if we have a map
        // $familyIdMap maps Access "Family ID" → our DB family_id

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $rowNum => $row) {
            try {
                $data = ($mode === 'assoc')
                    ? $this->mapAssocRow($row, $columnMapping)
                    : $this->mapRow($row, $columnMapping);

                // Resolve family link
                $ourFamilyId = null;

                // Try by family_number first
                $familyNumber = $data['_family_number'] ?? null;
                unset($data['_family_number']);
                if ($familyNumber && isset($familyByNumber[(int) $familyNumber])) {
                    $ourFamilyId = $familyByNumber[(int) $familyNumber];
                }

                // Try by Access Family ID if we have a map
                $accessFamilyId = $data['_family_id'] ?? null;
                unset($data['_family_id']);
                if (! $ourFamilyId && $accessFamilyId && $familyIdMap && isset($familyIdMap[$accessFamilyId])) {
                    $ourFamilyId = $familyIdMap[$accessFamilyId];
                }

                if (! $ourFamilyId) {
                    $skipped++;
                    continue;
                }

                $data['family_id'] = $ourFamilyId;
                $data['season_year'] = $seasonYear;

                if (isset($data['age'])) {
                    $data['age'] = (int) $data['age'];
                }
                if (isset($data['gift_level'])) {
                    $data['gift_level'] = (int) $data['gift_level'];
                }
                if (isset($data['mail_merged'])) {
                    $data['mail_merged'] = $this->castBool($data['mail_merged']);
                }

                Child::withoutGlobalScopes()->create($data);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($rowNum + 2) . ": " . $e->getMessage();
            }
        }

        return compact('imported', 'skipped', 'errors');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function buildColumnMapping(array $headers, array $columnMap): array
    {
        $mapping = [];
        foreach ($headers as $col => $header) {
            if ($header === null || $header === '') continue;
            $normalized = $this->normalizeHeader($header);
            if (isset($columnMap[$normalized])) {
                $mapping[$col] = $columnMap[$normalized];
            }
        }
        return $mapping;
    }

    private function mapRow(array $row, array $columnMapping): array
    {
        $data = [];
        foreach ($columnMapping as $col => $dbColumn) {
            $value = $row[$col] ?? null;
            if ($value !== null && $value !== '') {
                $data[$dbColumn] = $value;
            }
        }
        return $data;
    }

    private function mapAssocRow(array $row, array $columnMapping): array
    {
        $data = [];
        foreach ($columnMapping as $header => $dbColumn) {
            $value = $row[$header] ?? null;
            if ($value !== null && $value !== '') {
                $data[$dbColumn] = $value;
            }
        }
        return $data;
    }

    private function castBool(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        $str = strtolower(trim((string) $value));
        return in_array($str, ['yes', '1', 'true', 'y'], true);
    }
}
