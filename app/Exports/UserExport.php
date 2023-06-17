<?php

namespace App\Exports;

use App\Subscription;
use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
class UserExport implements FromCollection,WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $dateS = '2023-02-15';
        $dateE = '2023-03-15';
        $result = User::select('name','email','phone','subscription_id','status')
                          ->where('user_type','user')
                        ->whereBetween('created_at', [$dateS, $dateE])
                        ->get();
                        $result->each->setAppends([]);
        $result->loginUser =12344;
            return $result;
    }
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Subscription Status',
            'Status'
        ];
    }
    public function map($user): array
    {
        $sub_status = Subscription::where('subscription_id',$user->subscription_id)->first();
        return [
            $user->name,
            $user->email,
            $user->phone,
             (isset($sub_status)) ? $sub_status->status : '',
            ($user->status == 1) ? 'Active' : 'InActive'
        ];
    }
}
