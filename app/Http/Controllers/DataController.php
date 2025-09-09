<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\User;

class DataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showCsvUpload()
    {
        return view('data.csv-upload');
    }

    public function uploadCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        // Read CSV file
        $csvData = array_map('str_getcsv', file($path));
        $header = array_shift($csvData);

        // Validate CSV structure
        $expectedHeaders = ['title', 'description', 'status'];
        if (array_diff($expectedHeaders, array_map('strtolower', $header))) {
            return back()->withErrors(['csv_file' => 'Invalid CSV format. Expected headers: title, description, status']);
        }

        $errors = [];
        $validRows = [];
        $rowNumber = 2; // Start from row 2 (after header)

        foreach ($csvData as $row) {
            if (count($row) !== count($header)) {
                $errors[] = "Row {$rowNumber}: Invalid number of columns";
                $rowNumber++;
                continue;
            }

            $rowData = array_combine(array_map('strtolower', $header), $row);
            
            // Validate each row
            $rowValidator = Validator::make($rowData, [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'status' => 'required|in:0,1,active,inactive',
            ]);

            if ($rowValidator->fails()) {
                $errors[] = "Row {$rowNumber}: " . implode(', ', $rowValidator->errors()->all());
            } else {
                // Normalize status
                $status = strtolower($rowData['status']);
                $rowData['status'] = ($status === '1' || $status === 'active') ? 1 : 0;
                $validRows[] = $rowData;
            }
            
            $rowNumber++;
        }

        if (!empty($errors)) {
            return back()->withErrors(['csv_file' => 'CSV validation failed:<br>' . implode('<br>', $errors)]);
        }

        // Store valid rows in database
        DB::beginTransaction();
        try {
            $insertedCount = 0;
            foreach ($validRows as $rowData) {
                Post::create([
                    'title' => $rowData['title'],
                    'description' => $rowData['description'],
                    'status' => $rowData['status'],
                    'created_by' => auth()->user()->name,
                    'updated_by' => auth()->user()->name,
                ]);
                $insertedCount++;
            }
            
            DB::commit();
            return redirect()->route('posts.index')
                ->with('success', "Successfully imported {$insertedCount} posts from CSV file.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['csv_file' => 'Failed to import CSV data. Please try again.']);
        }
    }

    public function downloadExcel(Request $request)
    {
        // Get filtered posts based on search parameters
        $query = Post::with('user')->where('deleted_at', null);

        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Role-based filtering
        if (auth()->user()->role !== 0) {
            $query->where('created_by', auth()->user()->name);
        }

        $posts = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV content
        $csvContent = "ID,Title,Description,Status,Created By,Created At,Updated At\n";
        
        foreach ($posts as $post) {
            $csvContent .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $post->id,
                str_replace('"', '""', $post->title),
                str_replace('"', '""', $post->description),
                $post->status ? 'Active' : 'Inactive',
                str_replace('"', '""', $post->created_by),
                $post->created_at->format('Y-m-d H:i:s'),
                $post->updated_at->format('Y-m-d H:i:s')
            );
        }

        $filename = 'posts_export_' . date('Y-m-d_H-i-s') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
