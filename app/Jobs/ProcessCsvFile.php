<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Models\Person;
use App\Models\Country;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    private $jobId;
    private $filePath;

    public function __construct($jobId, $filePath)
    {
        $this->jobId = $jobId;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        DB::reconnect();
        $importJob = ImportJob::findOrFail($this->jobId);
        $importJob->update(['status' => 'processing']);

        try {
            $totalRows = $this->countCsvRows($this->filePath);
            $importJob->update(['total_rows' => $totalRows]);

            $this->processInChunks($importJob);

            $importJob->update(['status' => 'completed']);
        } catch (Exception $e) {
            $importJob->update([
                'status' => 'failed',
                'error_logs' => json_encode(['error' => $e->getMessage()])
            ]);

            throw $e;
        } finally {
            Storage::delete($this->filePath);
            DB::disconnect();
        }
    }

    private function processInChunks(ImportJob $importJob)
    {
        $chunkSize = 100;
        $header = null;
        $processedRows = 0;
        $batch = [];

        $stream = fopen(Storage::path($this->filePath), 'r');

        while (($row = fgetcsv($stream, 0, ";", "\n")) !== false) {
            if (!$header) {
                $header = $row;

                continue;
            }

            $batch[] = array_combine([
                'emso',
                'name',
                'country',
                'age',
                'description',
            ], $row);

            if (count($batch) >= $chunkSize) {
                $this->processBatch($batch, $importJob);
                $processedRows += count($batch);
                $importJob->update(['processed_rows' => $processedRows]);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->processBatch($batch, $importJob);
            $processedRows += count($batch);
            $importJob->update(['processed_rows' => $processedRows]);
        }

        fclose($stream);
    }

    private function processBatch(array $batch, ImportJob $importJob)
    {
        DB::beginTransaction();

        // insert only new countries
        $now = now();
        $countryNames = collect($batch)->pluck('country')->unique()->values()->toArray();
        $existingCountries = Country::whereIn('name', $countryNames)->get()->keyBy('name');

        $newCountries = collect($countryNames)->diff($existingCountries->keys())
            ->map(fn($name) => [
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray();

        if (!empty($newCountries)) {
            Country::insert($newCountries);
            $existingCountries = Country::whereIn('name', $countryNames)->get()->keyBy('name');
        }

        $emsos = collect($batch)->pluck('emso')->toArray();
        $existingPeople = Person::whereIn('emso', $emsos)
            ->get()
            ->keyBy('emso');

        try {
            $peopleToCreate = [];
            $peopleToUpdate = [];

            foreach ($batch as $row) {
                $validator = Validator::make($row, [
                    'emso' => 'required|string|min:13|max:13',
                    'name' => 'required|string|max:255',
                    'country' => 'required|string|max:255',
                    'age' => 'required|integer|max_digits:3',
                    'description' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    $this->logError($importJob, $row, $validator->errors());
                    continue;
                }

                $personData = [
                    'emso' => $row['emso'],
                    'name' => $row['name'],
                    'country_id' => $existingCountries[$row['country']]->id,
                    'age' => $row['age'],
                    'description' => $row['description'],
                    'updated_at' => $now,
                ];

                if (!isset($existingPeople[$row['emso']])) {
                    $peopleToCreate[] = array_merge($personData, ['created_at' => $now]);
                } else {
                    $peopleToUpdate[] = [
                        'emso' => $row['emso'],
                        'data' => $personData
                    ];
                }
            }

            if (!empty($peopleToCreate)) {
                Person::insert($peopleToCreate);
            }

            foreach ($peopleToUpdate as $update) {
                Person::where('emso', $update['emso'])
                    ->update($update['data']);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function logError(ImportJob $importJob, array $row, $errors)
    {
        $importJob->increment('failed_rows');
        $currentLogs = json_decode($importJob->error_logs ?? '[]', true);
        $currentLogs[] = [
            'row' => $row,
            'errors' => $errors->toArray(),
            'timestamp' => now()->toDateTimeString()
        ];
        $importJob->update(['error_logs' => json_encode($currentLogs)]);
    }

    private function countCsvRows($filePath)
    {
        $rowCount = 0;
        $handle = fopen(Storage::path($filePath), 'r');
        while (fgets($handle) !== false) $rowCount++;
        fclose($handle);
        return $rowCount - 1;
    }
}
