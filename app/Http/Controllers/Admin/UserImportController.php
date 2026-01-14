<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Hash;

class UserImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    // Show import form
    public function showForm()
    {
        return view('admin.users.import');
    }

    // Handle file upload and import
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'sheet_index' => 'nullable|integer|min:0'
        ], [
            'file.required' => 'Vui lòng chọn file Excel',
            'file.mimes' => 'File phải là Excel hoặc CSV'
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            
            // Get sheet index from request or use all sheets
            $sheetIndex = $request->input('sheet_index');
            
            if ($sheetIndex !== null) {
                // Import specific sheet
                $worksheet = $spreadsheet->getSheet($sheetIndex);
            } else {
                // Import all sheets
                $worksheet = $spreadsheet->getActiveSheet();
            }
            
            $rows = $worksheet->toArray();
            $imported = 0;
            $errors = [];
            $debugInfo = [];

            // Debug: Log total rows and first few rows
            $debugInfo[] = "Tổng số dòng: " . count($rows);
            if (count($rows) > 0) {
                $debugInfo[] = "Dòng 1 (header): " . json_encode($rows[0]);
                if (count($rows) > 1) {
                    $debugInfo[] = "Dòng 2 (dữ liệu): " . json_encode($rows[1]);
                }
            }

            // Skip header rows
            $headerSkipped = false;
            foreach ($rows as $index => $row) {
                // Skip completely empty rows
                $isEmptyRow = true;
                foreach ($row as $cell) {
                    if (!empty($cell)) {
                        $isEmptyRow = false;
                        break;
                    }
                }
                if ($isEmptyRow) continue;

                // Detect and skip header row (contains "Tên VĐV", "Email", etc.)
                if (!$headerSkipped) {
                    $name = trim($row[2] ?? '');
                    $email = trim($row[4] ?? '');
                    
                    // Check if this is header row
                    if (strpos(strtolower($name), 'tên') !== false || 
                        strpos(strtolower($email), 'email') !== false) {
                        $headerSkipped = true;
                        continue;
                    }
                    $headerSkipped = true; // Skip first non-empty row as header
                }

                try {
                    // Extract data from Excel columns
                    // Column A (index 0): có thể trống
                    // Column B (index 1): STT hoặc dữ liệu đầu
                    // Column C (index 2): Tên
                    // Column D (index 3): Năm sinh
                    // Column E (index 4): Email
                    // Column F (index 5): Quốc gia
                    // Column G (index 6): Loại vận động viên
                    
                    $name = trim($row[2] ?? '');  // Column C (Tên)
                    $birthYear = trim($row[3] ?? '');  // Column D (Năm sinh)
                    $email = trim($row[4] ?? '');  // Column E (Email)
                    $country = trim($row[5] ?? '');  // Column F (Quốc gia)
                    $athleteType = trim($row[6] ?? '');  // Column G (Loại vận động viên)

                    // Debug log
                    $debugInfo[] = "Dòng " . ($index + 1) . ": Name='{$name}', Email='{$email}'";

                    // Validate required fields
                    if (!$name || !$email) {
                        $errors[] = "Dòng " . ($index + 1) . ": Thiếu tên hoặc email (name='{$name}', email='{$email}')";
                        continue;
                    }

                    // Basic email validation (must contain @)
                    if (strpos($email, '@') === false) {
                        $errors[] = "Dòng " . ($index + 1) . ": Email '{$email}' phải chứa ký tự @";
                        continue;
                    }

                    // Check if user already exists
                    if (User::where('email', $email)->exists()) {
                        $errors[] = "Dòng " . ($index + 1) . ": Email {$email} đã tồn tại";
                        continue;
                    }

                    // Prepare athlete types array
                    $athleteTypesArray = null;
                    if (!empty($athleteType)) {
                        // Split by comma if multiple types
                        $athleteTypesArray = array_map('trim', explode(',', $athleteType));
                    }

                    // Create user
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make('password123'), // Default password
                        'status' => 'approved',
                        'role_type' => 'user',
                        'athlete_types' => $athleteTypesArray,
                    ]);

                    // Assign default role
                    $user->assignRole('user');

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Dòng " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            // Prepare success message
            $message = "Đã import thành công {$imported} người dùng.";
            if (!empty($errors)) {
                $message .= " | Lỗi: " . implode(" | ", array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " (và " . (count($errors) - 5) . " lỗi khác)";
                }
            }
            
            // Add debug info if no users imported
            if ($imported === 0 && !empty($debugInfo)) {
                $message .= "\n\nDebug Info:\n" . implode("\n", $debugInfo);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Lỗi khi đọc file: ' . $e->getMessage());
        }
    }
}
