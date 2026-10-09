<?php

namespace App\Services;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Translates the checkpoint forms into the normalised
 * `check_point_items` / `check_point_images` tables.
 *
 * The form posts parallel arrays keyed by a *stable index* so that removing a
 * row in the browser never shifts a photo onto the wrong job:
 *
 *   original_index[<sid>], pekerjaan_id[<sid>], input_manual[<sid>],
 *   deskripsi[<sid>], tanggal[<sid>], approve_status[<sid>],
 *   existing_img[<sid>][], img[<sid>][]
 */
class CheckPointSyncService
{
    /** Maximum photos accepted per job row. */
    public const MAX_IMAGES_PER_ITEM = 7;

    public function __construct(
        private readonly CheckPointImageUploader $uploader,
    ) {
    }

    /**
     * Replace the whole item tree of a batch with what the request contains.
     */
    public function syncFromRequest(CheckPoint $checkPoint, Request $request): void
    {
        $submitted = $this->normalise($request);

        $existing = $checkPoint->items()->with('images')->get()->keyBy('id');

        $survivingIds = [];

        foreach ($submitted as $urutan => $row) {
            $item = $row['id'] !== null ? $existing->get($row['id']) : null;

            if (! $item) {
                $item = new CheckPointItem(['check_point_id' => $checkPoint->id]);
            }

            $item->fill([
                'pekerjaan_cp_id' => $row['pekerjaan_cp_id'],
                'input_manual' => $row['input_manual'],
                'deskripsi' => $row['deskripsi'],
                'tanggal' => $row['tanggal'],
                'latitude' => $row['latitude'],
                'longtitude' => $row['longtitude'],
                'approve_status' => $row['approve_status'],
                'note' => $row['note'],
                'urutan' => $urutan,
            ]);

            $item->check_point_id = $checkPoint->id;
            $item->save();

            $survivingIds[] = $item->id;

            $this->syncImages($item, $row['keep_images'], $row['new_images']);
        }

        // Anything the form no longer mentions is deleted (images cascade).
        $existing
            ->reject(fn (CheckPointItem $item) => in_array($item->id, $survivingIds, true))
            ->each(function (CheckPointItem $item): void {
                $item->images->each(fn ($image) => Storage::disk('public')->delete('images/' . $image->path));
                $item->delete();
            });
    }

    /**
     * Merge new rows into an existing batch without touching the rows already
     * stored (used by the "upload bukti" flow for the current week).
     */
    public function appendFromRequest(CheckPoint $checkPoint, Request $request): void
    {
        $submitted = $this->normalise($request, keepIndexes: false, jobFromKey: true);

        if ($submitted === []) {
            return;
        }

        $nextUrutan = (int) $checkPoint->items()->max('urutan') + 1;

        foreach ($submitted as $row) {
            $item = new CheckPointItem([
                'check_point_id' => $checkPoint->id,
                'pekerjaan_cp_id' => $row['pekerjaan_cp_id'],
                'input_manual' => $row['input_manual'],
                'deskripsi' => $row['deskripsi'],
                'tanggal' => $row['tanggal'],
                'latitude' => $row['latitude'],
                'longtitude' => $row['longtitude'],
                'approve_status' => $row['approve_status'],
                'note' => $row['note'],
                'urutan' => $nextUrutan++,
            ]);
            $item->save();

            $this->syncImages($item, [], $row['new_images']);
        }
    }

    /**
     * Return a user-facing message when any job row carries more than the
     * allowed number of photos. Null means the request is within the limit.
     */
    public function imageLimitError(Request $request): ?string
    {
        foreach ((array) $request->file('img', []) as $entry) {
            $count = $entry instanceof UploadedFile
                ? 1
                : count(array_filter((array) $entry, fn ($file) => $file instanceof UploadedFile));

            if ($count > self::MAX_IMAGES_PER_ITEM) {
                return 'Foto maksimal ' . self::MAX_IMAGES_PER_ITEM . ' per pekerjaan, Anda memilih ' . $count . '.';
            }
        }

        return null;
    }

    /**
     * @return array<int, array{
     *     id: int|null,
     *     pekerjaan_cp_id: ?string,
     *     input_manual: ?string,
     *     deskripsi: ?string,
     *     tanggal: ?string,
     *     latitude: ?string,
     *     longtitude: ?string,
     *     approve_status: string,
     *     note: ?string,
     *     keep_images: array<int, string>,
     *     new_images: array<int, UploadedFile>
     * }>
     */
    private function normalise(Request $request, bool $keepIndexes = true, bool $jobFromKey = false): array
    {
        // Map stable row index => original_index value (the browser sends both
        // the array key and the value; the key is what groups the fields).
        $indexes = $this->rowIndexes($request);
        $jobs = (array) $request->input('pekerjaan_id', []);
        $manuals = (array) $request->input('input_manual', []);
        $descriptions = (array) $request->input('deskripsi', []);
        $dates = (array) $request->input('tanggal', []);
        $statuses = (array) $request->input('approve_status', []);
        $notes = (array) $request->input('note', []);
        $existingImages = (array) $request->input('existing_img', []);
        $newImages = (array) $request->file('img', []);

        $globalLatitude = $request->input('latitude');
        $globalLongitude = $request->input('longtitude');

        $rows = [];

        foreach ($indexes as $sid) {
            $job = $this->stringOrNull($jobs[$sid] ?? null);
            $manual = $this->stringOrNull($manuals[$sid] ?? null);
            $description = $this->stringOrNull($descriptions[$sid] ?? null);

            // A row with nothing at all is an artefact of the template.
            if ($job === null && $manual === null && $description === null && ! isset($newImages[$sid])) {
                continue;
            }

            // "manual" is a sentinel from the dropdown, not a real job id.
            if ($jobFromKey) {
                // uploadBukti keys each row as `pcp_<id>` (a planned job) or
                // `item_<id>` (an ad-hoc row copied from the plan), so the two
                // id spaces never collide inside the same form.
                [$pekerjaanId, $sourceManual] = $this->resolveKeyedJob($sid);
                $manual = $sourceManual ?? $manual;
            } else {
                $pekerjaanId = $job === 'manual' ? null : $job;
            }

            $keep = collect($existingImages[$sid] ?? [])
                ->filter(fn ($path) => is_string($path) && $path !== '')
                ->values()
                ->all();

            // A single-file input (no `[]`) arrives as one UploadedFile rather
            // than a list, so normalise before counting.
            $entry = $newImages[$sid] ?? [];
            if ($entry instanceof UploadedFile) {
                $entry = [$entry];
            }

            $uploads = collect((array) $entry)
                ->filter(fn ($file) => $file instanceof UploadedFile)
                ->values()
                ->all();

            if (count($uploads) > self::MAX_IMAGES_PER_ITEM) {
                // The controller checks first and shows a friendly toastr; this
                // guarantees the cap is never exceeded silently.
                throw new \RuntimeException('Foto maksimal ' . self::MAX_IMAGES_PER_ITEM . ' per pekerjaan.');
            }

            $rows[] = [
                'id' => $keepIndexes ? $this->intOrNull($request->input("item_id.{$sid}")) : null,
                'pekerjaan_cp_id' => $pekerjaanId,
                'input_manual' => $manual,
                'deskripsi' => $description,
                'tanggal' => $this->stringOrNull($dates[$sid] ?? null),
                'latitude' => $this->stringOrNull($globalLatitude),
                'longtitude' => $this->stringOrNull($globalLongitude),
                'approve_status' => $this->stringOrNull($statuses[$sid] ?? null) ?? 'proccess',
                'note' => $this->stringOrNull($notes[$sid] ?? null),
                'keep_images' => $keep,
                'new_images' => $uploads,
            ];
        }

        return $rows;
    }

    /**
     * Stable row indexes present in the request, ordered by their submitted
     * original_index value so the UI order is preserved.
     *
     * @return array<int, string>
     */
    private function rowIndexes(Request $request): array
    {
        $keys = array_merge(
            array_keys((array) $request->input('pekerjaan_id', [])),
            array_keys((array) $request->input('input_manual', [])),
            array_keys((array) $request->input('deskripsi', [])),
            array_keys((array) $request->file('img', [])),
        );

        $keys = array_values(array_unique($keys));

        usort($keys, function ($a, $b) {
            $ia = is_numeric($a) ? (int) $a : PHP_INT_MAX;
            $ib = is_numeric($b) ? (int) $b : PHP_INT_MAX;

            return $ia <=> $ib;
        });

        return $keys;
    }

    /**
     * Decode an uploadBukti row key into [pekerjaan_cp_id, input_manual].
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function resolveKeyedJob(string $key): array
    {
        if (str_starts_with($key, 'pcp_')) {
            return [$this->stringOrNull(substr($key, 4)), null];
        }

        if (str_starts_with($key, 'item_')) {
            $item = CheckPointItem::find(substr($key, 5));

            return [$item?->pekerjaan_cp_id, $item?->input_manual];
        }

        // Legacy rows posted a bare PekerjaanCp id as the key.
        return [$this->stringOrNull($key), null];
    }

    private function syncImages(CheckPointItem $item, array $keepPaths, array $uploads): void
    {
        $item->images()->whereNotIn('path', $keepPaths ?: [''])->each(function ($image): void {
            Storage::disk('public')->delete('images/' . $image->path);
            $image->delete();
        });

        if ($keepPaths !== []) {
            $item->images()->whereIn('path', $keepPaths)->get()->each(function ($image, $i): void {
                $image->urutan = $i;
                $image->save();
            });
        }

        $next = (int) $item->images()->max('urutan') + 1;
        foreach ($uploads as $file) {
            $item->images()->create([
                'path' => $this->uploader->store($file),
                'urutan' => $next++,
            ]);
        }
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
