<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait GeneralHelperTrait
{

    public function subscriptionNumber()
    {


        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $existing_subscription_number = DB::table('subscription_numbers')
            ->where('day', $day)
            ->first();

        if ($existing_subscription_number) {
            $newCode = $this->code($existing_subscription_number->code);
            $new_job_number = $existing_subscription_number->year . $this->monthWithleadingZero($existing_subscription_number->month) . $this->dayWithLeadingZero($existing_subscription_number->day) . $newCode;
            DB::table('subscription_numbers')
                ->where('day', $day)
                ->update([
                    'code' => $newCode
                ]);

        } else {
            DB::table('subscription_numbers')->truncate();
            DB::table('subscription_numbers')
                ->insert([
                    'year' => $year,
                    'month' => $month,
                    'day' => $day,
                    'code' => '01'
                ]);
            $new_job_number = $year . $month . $day . '01';
        }

        return $new_job_number;
    }

    public function code(string $code)
    {
        $convert_into_int = (int)$code;
        $incremented_int = $convert_into_int + 1;
        $integer_length = (int)log10($incremented_int) + 1;

        if ($integer_length === 1) {
            $new_code = '0' . $incremented_int;
        } else {
            $new_code = $incremented_int;
        }

        return $new_code;
    }

    public function monthWithleadingZero($month)
    {
        $month = (int)$month;
        $integer_length = (int)log10($month) + 1;

        if ($integer_length === 1) {
            $new_month = '0' . $month;
        } else {
            $new_month = $month;
        }

        return $new_month;
    }

    public function dayWithLeadingZero($day)
    {
        $day = (int)$day;
        $integer_length = (int)log10($day) + 1;

        if ($integer_length === 1) {
            $new_day = '0' . $day;
        } else {
            $new_day = $day;
        }

        return $new_day;
    }
}
