<?php

namespace App\Http\Controllers;

use App\Services\CommissionFee;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CommissionCalculatorController extends Controller
{
    private CommissionFee $commissionFee;

    public function __construct(CommissionFee $commissionFee)
    {
        $this->commissionFee = $commissionFee;
    }

    public function calculateCommission(Request $request)
    {
        $csv_file = $request->file('csv_file');
        $file_path = $csv_file->getPathname();
        $file = new \SplFileObject($file_path, 'r');
        $data = [];

        while (!$file->eof()) {
            $row = $file->fgetcsv();
            if ($row !== false) {
                $data[] = explode(",", $row[0]);
            }
        }
        $file = null;
        $fees = $this->commissionFee->getCommissionFee($data);
        return response()->json(['fees' => $fees], 200);
    }
}
