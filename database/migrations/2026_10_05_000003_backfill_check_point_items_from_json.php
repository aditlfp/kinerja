<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill legacy JSON-array columns on `check_points` into the normalised
 * `check_point_items` / `check_point_images` tables.
 *
 * Legacy layout (parallel arrays, aligned by index):
 *   pekerjaan_cp_id[i], input_manual[i], deskripsi[i], tanggal[i],
 *   latitude[i], longtitude[i], approve_status[i], note[i]
 *   img[i] = list of file names for that one item
 *
 * After backfilling, the parent batch is marked as migrated via the
 * `items_backfilled` marker column so this migration is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('check_point_items')) {
            return;
        }

        $json = static function ($value): array {
            if (is_array($value)) {
                return $value;
            }
            if ($value === null || $value === '') {
                return [];
            }
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            // Plain scalar (legacy varchar written without json encoding).
            return [$value];
        };

        DB::table('check_points')
            ->orderBy('id')
            ->chunk(100, function ($rows) use ($json): void {
                foreach ($rows as $row) {
                    // Skip batches that already have normalised items.
                    if (DB::table('check_point_items')->where('check_point_id', $row->id)->exists()) {
                        continue;
                    }

                    $jobIds = $json($row->pekerjaan_cp_id ?? null);
                    $manuals = $json($row->input_manual ?? null);
                    $descriptions = $json($row->deskripsi ?? null);
                    $dates = $json($row->tanggal ?? null);
                    $latitudes = $json($row->latitude ?? null);
                    $longitudes = $json($row->longtitude ?? null);
                    $statuses = $json($row->approve_status ?? null);
                    $notes = $json($row->note ?? null);
                    $images = $json($row->img ?? null);

                    // `img` legacy is list-of-lists, but some rows stored a flat
                    // list of file names that all belong to the first item.
                    $imagesAreNested = collect($images)->contains(fn ($value) => is_array($value));

                    $rowCount = max(
                        count($jobIds),
                        count($manuals),
                        count($descriptions),
                        count($dates),
                        count($statuses),
                        1,
                    );

                    $now = now();

                    for ($i = 0; $i < $rowCount; $i++) {
                        $itemId = DB::table('check_point_items')->insertGetId([
                            'check_point_id' => $row->id,
                            'pekerjaan_cp_id' => $jobIds[$i] ?? null,
                            'input_manual' => $manuals[$i] ?? null,
                            'deskripsi' => $descriptions[$i] ?? null,
                            'tanggal' => $dates[$i] ?? null,
                            'latitude' => $latitudes[$i] ?? null,
                            'longtitude' => $longitudes[$i] ?? null,
                            'approve_status' => $statuses[$i] ?? 'proccess',
                            'note' => $notes[$i] ?? null,
                            'urutan' => $i,
                            'created_at' => $row->created_at ?? $now,
                            'updated_at' => $row->updated_at ?? $now,
                        ]);

                        if ($imagesAreNested) {
                            $itemImages = is_array($images[$i] ?? null) ? $images[$i] : [];
                        } elseif (count($images) === $rowCount) {
                            // Flat list that lines up 1:1 with the jobs.
                            $itemImages = isset($images[$i]) ? [$images[$i]] : [];
                        } else {
                            // Flat pool with no reliable alignment: keep them together.
                            $itemImages = $i === 0 ? $images : [];
                        }

                        $urutan = 0;
                        foreach (array_filter($itemImages, fn ($path) => is_string($path) && $path !== '') as $path) {
                            DB::table('check_point_images')->insert([
                                'check_point_item_id' => $itemId,
                                'path' => $path,
                                'urutan' => $urutan++,
                                'created_at' => $row->created_at ?? $now,
                                'updated_at' => $row->updated_at ?? $now,
                            ]);
                        }
                    }
                }
            });
    }

    public function down(): void
    {
        DB::table('check_point_images')->delete();
        DB::table('check_point_items')->delete();
    }
};
