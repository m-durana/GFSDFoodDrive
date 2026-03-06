<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $currentYear = (int) Setting::get('season_year', date('Y'));
        $seasons = Season::orderBy('year')->get();

        // Compute current season stats live
        $currentStats = Season::computeDetailedStats($currentYear);

        // Build multi-year data arrays for charts
        $years = $seasons->pluck('year')->toArray();
        $years[] = $currentYear; // Include current year

        $allYearStats = [];
        foreach ($seasons as $season) {
            $allYearStats[$season->year] = [
                'total_families' => $season->total_families,
                'total_children' => $season->total_children,
                'total_family_members' => $season->total_family_members,
                'total_adults' => $season->total_adults ?? 0,
                'gifts_level_0' => $season->gifts_level_0,
                'gifts_level_1' => $season->gifts_level_1,
                'gifts_level_2' => $season->gifts_level_2,
                'gifts_level_3' => $season->gifts_level_3,
                'deliveries_completed' => $season->deliveries_completed,
                'pickups_completed' => $season->pickups_completed ?? 0,
                'tags_adopted' => $season->tags_adopted,
                'adoption_rate' => $season->adoption_rate ?? ($season->total_children > 0 ? round(($season->tags_adopted / $season->total_children) * 100, 1) : 0),
                'avg_family_size' => $season->avg_family_size ?? ($season->total_families > 0 ? round($season->total_family_members / $season->total_families, 1) : 0),
                'avg_children_per_family' => $season->avg_children_per_family ?? ($season->total_families > 0 ? round($season->total_children / $season->total_families, 1) : 0),
                'families_severe_need' => $season->families_severe_need ?? 0,
                'families_with_pets' => $season->families_with_pets ?? 0,
                'families_needing_baby_supplies' => $season->families_needing_baby_supplies ?? 0,
                'children_by_age_group' => $season->children_by_age_group ?? [],
                'families_by_school' => $season->families_by_school ?? [],
                'families_by_size' => $season->families_by_size ?? [],
                'families_by_language' => $season->families_by_language ?? [],
                'warehouse_stats' => $season->warehouse_stats ?? [],
            ];
        }

        // Add current year stats
        $allYearStats[$currentYear] = $currentStats;

        // Compute growth rates
        $growthRates = [];
        $sortedYears = array_keys($allYearStats);
        sort($sortedYears);
        for ($i = 1; $i < count($sortedYears); $i++) {
            $prevYear = $sortedYears[$i - 1];
            $thisYear = $sortedYears[$i];
            $prev = $allYearStats[$prevYear];
            $curr = $allYearStats[$thisYear];

            $growthRates[$thisYear] = [
                'families' => $prev['total_families'] > 0 ? round((($curr['total_families'] - $prev['total_families']) / $prev['total_families']) * 100, 1) : null,
                'children' => $prev['total_children'] > 0 ? round((($curr['total_children'] - $prev['total_children']) / $prev['total_children']) * 100, 1) : null,
            ];
        }

        // Summary KPIs (all-time)
        $allTimeFamilies = array_sum(array_column($allYearStats, 'total_families'));
        $allTimeChildren = array_sum(array_column($allYearStats, 'total_children'));
        $allTimePeople = array_sum(array_column($allYearStats, 'total_family_members'));
        $totalYears = count($sortedYears);

        $kpis = [
            'all_time_families' => $allTimeFamilies,
            'all_time_children' => $allTimeChildren,
            'all_time_people' => $allTimePeople,
            'total_years' => $totalYears,
            'avg_families_per_year' => $totalYears > 0 ? round($allTimeFamilies / $totalYears) : 0,
            'current_year_growth_families' => $growthRates[$currentYear]['families'] ?? null,
            'current_year_growth_children' => $growthRates[$currentYear]['children'] ?? null,
        ];

        return view('santa.analytics', compact(
            'currentYear', 'seasons', 'allYearStats', 'sortedYears',
            'growthRates', 'kpis', 'currentStats'
        ));
    }

    /**
     * Export analytics data as CSV.
     */
    public function export(Request $request)
    {
        $currentYear = (int) Setting::get('season_year', date('Y'));
        $seasons = Season::orderBy('year')->get();

        $rows = [];
        $rows[] = ['Year', 'Families', 'Children', 'Adults', 'Total People', 'Avg Family Size', 'Deliveries', 'Tags Adopted', 'Adoption Rate %', 'Gift Level 0', 'Gift Level 1', 'Gift Level 2', 'Gift Level 3'];

        foreach ($seasons as $s) {
            $rows[] = [
                $s->year, $s->total_families, $s->total_children, $s->total_adults ?? 0,
                $s->total_family_members, $s->avg_family_size ?? 0, $s->deliveries_completed,
                $s->tags_adopted, $s->adoption_rate ?? 0,
                $s->gifts_level_0, $s->gifts_level_1, $s->gifts_level_2, $s->gifts_level_3,
            ];
        }

        // Current year
        $cs = Season::computeDetailedStats($currentYear);
        $rows[] = [
            $currentYear, $cs['total_families'], $cs['total_children'], $cs['total_adults'],
            $cs['total_family_members'], $cs['avg_family_size'], $cs['deliveries_completed'],
            $cs['tags_adopted'], $cs['adoption_rate'],
            $cs['gifts_level_0'], $cs['gifts_level_1'], $cs['gifts_level_2'], $cs['gifts_level_3'],
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics-export.csv"',
        ]);
    }
}
