<?php

namespace App\Http\Controllers;

use App\Events\PusherBroadcast;
use App\Jobs\ProcessCsvFile;
use App\Models\ImportJob;
use Exception;
use Illuminate\Http\Request;

class CsvImportController extends Controller
{
    public function file_upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file',
            ]);

            $file = $request->file('file');
            if ($file->clientExtension() != 'csv') {
                return response()->json([
                    'message' => "Neveljavna končnica datoteke. Zahtevana je csv končnica.",
                    'success' => false,
                ], 500);
            }

            $path = $file->storeAs('uploads', $file->getClientOriginalName());

            $importJob = ImportJob::create([
                'filename' => $path,
                'status' => 'pending',
                'total_rows' => 0,
                'processed_rows' => 0,
            ]);

            event(new PusherBroadcast('Uvoz se je začel', 'success'));
            ProcessCsvFile::dispatch($importJob->id, $path);

            return response()->json([
                'message' => 'Datoteka uspešno naložena',
                'path' => $path,
                'success' => true,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}
