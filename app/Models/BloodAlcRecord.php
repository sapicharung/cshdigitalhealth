<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodAlcRecord extends Model
{
    use HasFactory;

    protected $table = 'blood_alc_records';

    protected $fillable = [
        'order_no',
        'month_year',
        'test_day',
        'hn',
        'patient_name',
        'police_station',
        'police_officer',
        'contact_phone',
        'doc_k8',
        'doc_pher',
        'doc_lab_send',
        'lab_send_date',
        'hosxp_result_date',
        'claim_status',
        'claim_date',
        'payment_received_date',
        'remarks',
    ];

    /**
     * Get the formatted test date in dd/mm/yyyy (Buddhist Era) format.
     */
    public function getFormattedTestDateAttribute(): string
    {
        $day = $this->test_day;
        $monthYear = $this->month_year;

        if (empty($day) && empty($monthYear)) {
            return '-';
        }

        // If day or monthYear is already in dd/mm/yyyy format
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', trim($day ?? ''))) {
            return trim($day);
        }
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', trim($monthYear ?? ''))) {
            return trim($monthYear);
        }

        $monthMap = [
            'ม.ค.' => '01', 'ม.ค' => '01', 'มกราคม' => '01',
            'ก.พ.' => '02', 'ก.พ' => '02', 'กุมภาพันธ์' => '02',
            'มี.ค.' => '03', 'มี.ค' => '03', 'มีนาคม' => '03',
            'เม.ย.' => '04', 'เม.ย' => '04', 'เมษายน' => '04',
            'พ.ค.' => '05', 'พ.ค' => '05', 'พฤษภาคม' => '05',
            'มิ.ย.' => '06', 'มิ.ย' => '06', 'มิถุนายน' => '06',
            'ก.ค.' => '07', 'ก.ค' => '07', 'กรกฎาคม' => '07',
            'ส.ค.' => '08', 'ส.ค' => '08', 'สิงหาคม' => '08',
            'ก.ย.' => '09', 'ก.ย' => '09', 'กันยายน' => '09',
            'ต.ค.' => '10', 'ต.ค' => '10', 'ตุลาคม' => '10',
            'พ.ย.' => '11', 'พ.ย' => '11', 'พฤศจิกายน' => '11',
            'ธ.ค.' => '12', 'ธ.ค' => '12', 'ธันวาคม' => '12',
        ];

        $d = !empty($day) ? sprintf('%02d', (int)preg_replace('/[^0-9]/', '', $day)) : '00';
        $m = '00';
        $y = '0000';

        if (!empty($monthYear)) {
            if (preg_match('/^([^\d]+)\s*(\d{2,4})$/u', trim($monthYear), $matches)) {
                $monthPart = trim($matches[1]);
                $yearPart = (int)$matches[2];

                if (isset($monthMap[$monthPart])) {
                    $m = $monthMap[$monthPart];
                } else {
                    foreach ($monthMap as $prefix => $mNum) {
                        if (str_starts_with($monthPart, rtrim($prefix, '.'))) {
                            $m = $mNum;
                            break;
                        }
                    }
                }

                if ($yearPart < 100) {
                    $y = 2500 + $yearPart;
                } elseif ($yearPart < 2400) {
                    $y = $yearPart + 543;
                } else {
                    $y = $yearPart;
                }
            }
        }

        if ($d !== '00' && $m !== '00' && $y !== '0000') {
            return "{$d}/{$m}/{$y}";
        }

        return trim(($day ? $day . ' ' : '') . ($monthYear ?? ''));
    }
}
