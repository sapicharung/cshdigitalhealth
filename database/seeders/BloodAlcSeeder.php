<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BloodAlcRecord;

class BloodAlcSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = base_path('bloodalc.csv');
        if (!file_exists($csvFile)) {
            $this->command->warn("bloodalc.csv not found.");
            return;
        }

        $fp = fopen($csvFile, 'r');
        if (!$fp) return;

        // Skip header lines (Row 1-3 in CSV header)
        // Let's inspect first rows carefully
        $imported = 0;
        while (($row = fgetcsv($fp)) !== false) {
            // Check if row has valid patient name or HN
            $orderNo = isset($row[0]) ? trim($row[0]) : '';
            $patientName = isset($row[4]) ? trim($row[4]) : '';

            // Ignore header rows and empty rows
            if (!is_numeric($orderNo) || empty($patientName)) {
                continue;
            }

            BloodAlcRecord::updateOrCreate(
                [
                    'order_no' => (int)$orderNo,
                ],
                [
                    'month_year'            => isset($row[1]) && trim($row[1]) !== '' ? trim($row[1]) : null,
                    'test_day'              => isset($row[2]) && trim($row[2]) !== '' ? trim($row[2]) : null,
                    'hn'                    => isset($row[3]) && trim($row[3]) !== '' ? trim($row[3]) : null,
                    'patient_name'          => $patientName,
                    'police_station'        => isset($row[5]) && trim($row[5]) !== '' ? trim($row[5]) : null,
                    'police_officer'        => isset($row[6]) && trim($row[6]) !== '' ? trim($row[6]) : null,
                    'contact_phone'         => isset($row[7]) && trim($row[7]) !== '' ? trim($row[7]) : null,
                    'doc_k8'                => isset($row[8]) && trim($row[8]) !== '' ? trim($row[8]) : 'ไม่มี',
                    'doc_pher'              => isset($row[9]) && trim($row[9]) !== '' ? trim($row[9]) : 'ไม่มี',
                    'doc_lab_send'          => isset($row[10]) && trim($row[10]) !== '' ? trim($row[10]) : 'ไม่มี',
                    'lab_send_date'         => isset($row[11]) && trim($row[11]) !== '' ? trim($row[11]) : null,
                    'hosxp_result_date'     => isset($row[12]) && trim($row[12]) !== '' ? trim($row[12]) : null,
                    'claim_status'          => isset($row[13]) && trim($row[13]) !== '' ? trim($row[13]) : null,
                    'claim_date'            => isset($row[14]) && trim($row[14]) !== '' ? trim($row[14]) : null,
                    'payment_received_date' => isset($row[15]) && trim($row[15]) !== '' ? trim($row[15]) : null,
                    'remarks'               => isset($row[16]) && trim($row[16]) !== '' ? trim($row[16]) : null,
                ]
            );
            $imported++;
        }
        fclose($fp);

        $this->command->info("Imported {$imported} BloodAlc records successfully!");
    }
}
