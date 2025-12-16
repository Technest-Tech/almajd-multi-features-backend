<?php

namespace Database\Seeders\Helpers;

class OldDataAnalyzer
{
    private array $stats = [];

    public function analyze(string $dataPath): array
    {
        $this->stats = [
            'currencies' => $this->analyzeCurrencies($dataPath . '/currencies.sql'),
            'users' => $this->analyzeUsers($dataPath . '/users.sql'),
            'families' => $this->analyzeFamilies($dataPath . '/families.sql'),
            'family_tutor' => $this->analyzeFamilyTutor($dataPath . '/family_tutor.sql'),
        ];

        return $this->stats;
    }

    private function analyzeCurrencies(string $file): array
    {
        $content = file_get_contents($file);
        preg_match_all('/INSERT INTO `currencies`.*?VALUES\s*(.*?);/s', $content, $matches);
        
        $records = [];
        if (!empty($matches[1])) {
            $data = $matches[1][0];
            preg_match_all('/\((\d+),\s*\'([^\']+)\',\s*\'([^\']+)\'\)/', $data, $recordMatches);
            
            for ($i = 0; $i < count($recordMatches[0]); $i++) {
                $records[] = [
                    'id' => $recordMatches[1][$i],
                    'name' => $recordMatches[2][$i],
                    'symbol' => $recordMatches[3][$i],
                ];
            }
        }

        return [
            'count' => count($records),
            'records' => $records,
        ];
    }

    private function analyzeUsers(string $file): array
    {
        $content = file_get_contents($file);
        
        // Count total records
        preg_match_all('/\(\d+,/', $content, $matches);
        $totalCount = count($matches[0]);
        
        // Count by user_type_id
        preg_match_all('/\'[^\']+\',\s*(\d+),/', $content, $typeMatches);
        $userTypes = array_count_values($typeMatches[1]);

        return [
            'count' => $totalCount,
            'admins' => $userTypes[0] ?? 0,
            'teachers' => $userTypes[1] ?? 0,
            'id_range' => $this->extractIdRange($content),
        ];
    }

    private function analyzeFamilies(string $file): array
    {
        $content = file_get_contents($file);
        
        // Count total records
        preg_match_all('/\(\d+,\s*\'/', $content, $matches);
        $totalCount = count($matches[0]);
        
        // Extract currency distribution
        preg_match_all('/,\s*(\d+),\s*\'[\d-]+ [\d:]+\'/', $content, $currencyMatches);
        $currencies = array_count_values($currencyMatches[1]);
        
        // Extract country code patterns
        preg_match_all('/\'\+(\d+)/', $content, $countryMatches);
        $countryCodes = array_slice(array_count_values($countryMatches[1]), 0, 10, true);

        return [
            'count' => $totalCount,
            'currency_distribution' => $currencies,
            'top_country_codes' => $countryCodes,
            'id_range' => $this->extractIdRange($content),
        ];
    }

    private function analyzeFamilyTutor(string $file): array
    {
        $content = file_get_contents($file);
        
        // Count total records
        preg_match_all('/\(\d+,\s*\d+,\s*\d+,/', $content, $matches);
        $totalCount = count($matches[0]);
        
        // Extract user and family IDs
        preg_match_all('/\((\d+),\s*(\d+),\s*(\d+),/', $content, $relationMatches);
        
        $uniqueTeachers = !empty($relationMatches[2]) ? count(array_unique($relationMatches[2])) : 0;
        $uniqueStudents = !empty($relationMatches[3]) ? count(array_unique($relationMatches[3])) : 0;

        return [
            'count' => $totalCount,
            'unique_teachers' => $uniqueTeachers,
            'unique_students' => $uniqueStudents,
        ];
    }

    private function extractIdRange(string $content): array
    {
        preg_match_all('/\((\d+),/', $content, $matches);
        $ids = array_map('intval', $matches[1]);
        
        return [
            'min' => !empty($ids) ? min($ids) : 0,
            'max' => !empty($ids) ? max($ids) : 0,
        ];
    }

    public function generateReport(): string
    {
        $report = "\n=== OLD DATABASE ANALYSIS REPORT ===\n\n";
        
        $report .= "CURRENCIES:\n";
        $report .= "  Total: {$this->stats['currencies']['count']}\n";
        foreach ($this->stats['currencies']['records'] as $currency) {
            $report .= "  - {$currency['id']}: {$currency['name']} ({$currency['symbol']})\n";
        }
        
        $report .= "\nUSERS (Teachers/Admins):\n";
        $report .= "  Total: {$this->stats['users']['count']}\n";
        $report .= "  Admins: {$this->stats['users']['admins']}\n";
        $report .= "  Teachers: {$this->stats['users']['teachers']}\n";
        $report .= "  ID Range: {$this->stats['users']['id_range']['min']} - {$this->stats['users']['id_range']['max']}\n";
        
        $report .= "\nFAMILIES (Students):\n";
        $report .= "  Total: {$this->stats['families']['count']}\n";
        $report .= "  ID Range: {$this->stats['families']['id_range']['min']} - {$this->stats['families']['id_range']['max']}\n";
        $report .= "  Currency Distribution:\n";
        foreach ($this->stats['families']['currency_distribution'] as $currencyId => $count) {
            $report .= "    Currency ID {$currencyId}: {$count} families\n";
        }
        $report .= "  Top Country Codes:\n";
        foreach ($this->stats['families']['top_country_codes'] as $code => $count) {
            $report .= "    +{$code}: {$count} families\n";
        }
        
        $report .= "\nFAMILY_TUTOR (Relationships):\n";
        $report .= "  Total Relationships: {$this->stats['family_tutor']['count']}\n";
        $report .= "  Unique Teachers: {$this->stats['family_tutor']['unique_teachers']}\n";
        $report .= "  Unique Students: {$this->stats['family_tutor']['unique_students']}\n";
        
        $report .= "\n=== END REPORT ===\n\n";
        
        return $report;
    }
}


