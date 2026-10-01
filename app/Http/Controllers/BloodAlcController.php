<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BloodAlcRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BloodAlcController extends Controller
{
    /**
     * Display a listing of the blood alcohol test records.
     */
    public function index(Request $request)
    {
        $query = BloodAlcRecord::query();

        // Search Keyword
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('hn', 'like', "%{$search}%")
                  ->orWhere('police_station', 'like', "%{$search}%")
                  ->orWhere('police_officer', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%");
            });
        }

        // Filter by Month
        if ($request->filled('month_year')) {
            $query->where('month_year', $request->month_year);
        }

        // Filter by Claim Status
        if ($request->filled('claim_status')) {
            if ($request->claim_status === 'pending') {
                $query->where(function ($q) {
                    $q->whereNull('claim_status')
                      ->orWhere('claim_status', '')
                      ->orWhere('claim_status', 'like', '%รอ%');
                });
            } elseif ($request->claim_status === 'paid') {
                $query->whereNotNull('payment_received_date')
                      ->where('payment_received_date', '!=', '');
            } else {
                $query->where('claim_status', $request->claim_status);
            }
        }

        // Filter by Police Station
        if ($request->filled('police_station')) {
            $query->where('police_station', $request->police_station);
        }

        // Calculate KPI Statistics
        $totalCount = BloodAlcRecord::count();
        $claimedCount = BloodAlcRecord::where('claim_status', 'เบิกจ่ายแล้ว')->count();
        $paymentReceivedCount = BloodAlcRecord::whereNotNull('payment_received_date')
            ->where('payment_received_date', '!=', '')
            ->count();
        $cancelledCount = BloodAlcRecord::where('claim_status', 'ยกเลิก')->count();
        $pendingCount = BloodAlcRecord::where(function ($q) {
            $q->whereNull('claim_status')
              ->orWhere('claim_status', '')
              ->orWhere('claim_status', 'like', '%รอ%');
        })->count();

        // Dropdown distinct filter lists
        $monthList = BloodAlcRecord::whereNotNull('month_year')
            ->where('month_year', '!=', '')
            ->distinct()
            ->orderBy('id', 'asc')
            ->pluck('month_year');

        $stationList = BloodAlcRecord::whereNotNull('police_station')
            ->where('police_station', '!=', '')
            ->distinct()
            ->pluck('police_station');

        // Records list
        $records = $query->orderByRaw('order_no IS NULL, order_no ASC, id ASC')
                         ->paginate(50)
                         ->withQueryString();

        // Next order_no for creation modal
        $maxOrderNo = BloodAlcRecord::max('order_no') ?? 0;
        $nextOrderNo = $maxOrderNo + 1;

        return view('blood_alc.index', compact(
            'records',
            'totalCount',
            'claimedCount',
            'paymentReceivedCount',
            'pendingCount',
            'cancelledCount',
            'monthList',
            'stationList',
            'nextOrderNo'
        ));
    }

    /**
     * Store a newly created record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_name'          => 'required|string|max:255',
            'order_no'              => 'nullable|integer',
            'month_year'            => 'nullable|string|max:50',
            'test_day'              => 'nullable|string|max:50',
            'hn'                    => 'nullable|string|max:50',
            'police_station'        => 'nullable|string|max:255',
            'police_officer'        => 'nullable|string|max:255',
            'contact_phone'         => 'nullable|string|max:50',
            'doc_k8'                => 'nullable|string|max:50',
            'doc_pher'              => 'nullable|string|max:50',
            'doc_lab_send'          => 'nullable|string|max:50',
            'lab_send_date'         => 'nullable|string|max:100',
            'hosxp_result_date'     => 'nullable|string|max:100',
            'claim_status'          => 'nullable|string|max:100',
            'claim_date'            => 'nullable|string|max:100',
            'payment_received_date' => 'nullable|string|max:100',
            'remarks'               => 'nullable|string',
        ]);

        if (empty($validated['order_no'])) {
            $max = BloodAlcRecord::max('order_no') ?? 0;
            $validated['order_no'] = $max + 1;
        }

        // Auto-pad HN to 9 digits if numeric
        if (!empty($validated['hn'])) {
            $cleanHn = trim($validated['hn']);
            if (ctype_digit($cleanHn) && strlen($cleanHn) <= 9) {
                $validated['hn'] = str_pad($cleanHn, 9, '0', STR_PAD_LEFT);
            }
        }

        $validated['doc_k8'] = $request->input('doc_k8');
        $validated['doc_pher'] = $request->input('doc_pher');
        $validated['doc_lab_send'] = $request->input('doc_lab_send');

        BloodAlcRecord::create($validated);

        return redirect()->route('blood-alc.index')
                         ->with('success', 'บันทึกข้อมูลการส่งตรวจเรียบร้อยแล้ว');
    }

    /**
     * Lookup patient from HOSxP database (cshos connection) by HN.
     */
    public function lookupPatient(Request $request)
    {
        $hn = trim($request->get('hn', ''));
        if (empty($hn)) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาระบุ HN'
            ]);
        }

        // Automatically pad to 9 digits if numeric
        if (ctype_digit($hn) && strlen($hn) <= 9) {
            $hn = str_pad($hn, 9, '0', STR_PAD_LEFT);
        }

        try {
            $patient = DB::connection('cshos')
                ->table('patient')
                ->where('hn', $hn)
                ->first(['hn', 'pname', 'fname', 'lname', 'cid', 'birthday']);

            if ($patient) {
                $pname = trim($patient->pname ?? '');
                $fname = trim($patient->fname ?? '');
                $lname = trim($patient->lname ?? '');
                $fullName = trim("{$pname}{$fname} {$lname}");

                return response()->json([
                    'success'      => true,
                    'hn'           => $patient->hn,
                    'patient_name' => $fullName,
                    'pname'        => $pname,
                    'fname'        => $fname,
                    'lname'        => $lname,
                    'cid'          => $patient->cid ?? '',
                    'birthday'     => $patient->birthday ?? '',
                ]);
            }

            return response()->json([
                'success' => false,
                'hn'      => $hn,
                'message' => 'ไม่พบข้อมูลผู้ป่วยในระบบ HOSxP สำหรับ HN: ' . $hn
            ]);
        } catch (\Exception $e) {
            Log::warning('HOSxP Patient lookup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'hn'      => $hn,
                'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูล HOSxP ได้: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return a record as JSON for edit modal.
     */
    public function show($id)
    {
        $record = BloodAlcRecord::findOrFail($id);
        return response()->json($record);
    }

    /**
     * Update the specified record in storage.
     */
    public function update(Request $request, $id)
    {
        $record = BloodAlcRecord::findOrFail($id);

        $validated = $request->validate([
            'patient_name'          => 'required|string|max:255',
            'order_no'              => 'nullable|integer',
            'month_year'            => 'nullable|string|max:50',
            'test_day'              => 'nullable|string|max:50',
            'hn'                    => 'nullable|string|max:50',
            'police_station'        => 'nullable|string|max:255',
            'police_officer'        => 'nullable|string|max:255',
            'contact_phone'         => 'nullable|string|max:50',
            'doc_k8'                => 'nullable|string|max:50',
            'doc_pher'              => 'nullable|string|max:50',
            'doc_lab_send'          => 'nullable|string|max:50',
            'lab_send_date'         => 'nullable|string|max:100',
            'hosxp_result_date'     => 'nullable|string|max:100',
            'claim_status'          => 'nullable|string|max:100',
            'claim_date'            => 'nullable|string|max:100',
            'payment_received_date' => 'nullable|string|max:100',
            'remarks'               => 'nullable|string',
        ]);

        // Auto-pad HN to 9 digits if numeric
        if (!empty($validated['hn'])) {
            $cleanHn = trim($validated['hn']);
            if (ctype_digit($cleanHn) && strlen($cleanHn) <= 9) {
                $validated['hn'] = str_pad($cleanHn, 9, '0', STR_PAD_LEFT);
            }
        }

        $validated['doc_k8'] = $request->input('doc_k8');
        $validated['doc_pher'] = $request->input('doc_pher');
        $validated['doc_lab_send'] = $request->input('doc_lab_send');

        $record->update($validated);

        return redirect()->route('blood-alc.index')
                         ->with('success', 'อัปเดตข้อมูลลำดับที่ ' . $record->order_no . ' เรียบร้อยแล้ว');
    }

    /**
     * Remove the specified record from storage.
     */
    public function destroy($id)
    {
        $record = BloodAlcRecord::findOrFail($id);
        $orderNo = $record->order_no;
        $record->delete();

        return redirect()->route('blood-alc.index')
                         ->with('success', 'ลบข้อมูลลำดับที่ ' . $orderNo . ' เรียบร้อยแล้ว');
    }

    /**
     * Export records to CSV with UTF-8 BOM for Microsoft Excel.
     */
    public function export(Request $request)
    {
        $query = BloodAlcRecord::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('hn', 'like', "%{$search}%")
                  ->orWhere('police_station', 'like', "%{$search}%")
                  ->orWhere('police_officer', 'like', "%{$search}%");
            });
        }
        if ($request->filled('month_year')) {
            $query->where('month_year', $request->month_year);
        }
        if ($request->filled('claim_status')) {
            if ($request->claim_status === 'pending') {
                $query->where(function ($q) {
                    $q->whereNull('claim_status')
                      ->orWhere('claim_status', '')
                      ->orWhere('claim_status', 'like', '%รอ%');
                });
            } else {
                $query->where('claim_status', $request->claim_status);
            }
        }
        if ($request->filled('police_station')) {
            $query->where('police_station', $request->police_station);
        }

        $records = $query->orderByRaw('order_no IS NULL, order_no ASC, id ASC')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="blood_alcohol_records_' . date('Ymd_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return new StreamedResponse(function () use ($records) {
            $fp = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel
            fputs($fp, "\xEF\xBB\xBF");

            // Header row
            fputcsv($fp, [
                'ลำดับ',
                'เดือน',
                'วันที่ตรวจ',
                'HN/หมายเลขตัวอย่าง',
                'ชื่อผู้เข้ารับการตรวจปริมาณแอลกอฮอล์ในเลือด',
                'สถานีตำรวจที่ส่งตรวจ',
                'ชื่อ-สกุล ตำรวจที่ร้องขอ',
                'เบอร์โทรติดต่อ',
                'ใบ ค.8',
                'ใบ Pher',
                'ใบนำส่งตรวจทางห้องปฏิบัติการ',
                'วันที่ส่งตรวจ',
                'วันที่ออกผลใน HosXP',
                'สถานะการเบิกจ่าย',
                'วันที่เบิกจ่าย',
                'วันที่ได้รับเงิน',
                'หมายเหตุ'
            ]);

            foreach ($records as $r) {
                fputcsv($fp, [
                    $r->order_no,
                    $r->month_year,
                    $r->test_day,
                    $r->hn,
                    $r->patient_name,
                    $r->police_station,
                    $r->police_officer,
                    $r->contact_phone,
                    $r->doc_k8,
                    $r->doc_pher,
                    $r->doc_lab_send,
                    $r->lab_send_date,
                    $r->hosxp_result_date,
                    $r->claim_status,
                    $r->claim_date,
                    $r->payment_received_date,
                    $r->remarks,
                ]);
            }
            fclose($fp);
        }, 200, $headers);
    }
}
