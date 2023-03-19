<?php

namespace App\Services;

use App\Enums\CurrencyName;
use App\Enums\UserOperation;
use App\Enums\UserType;

class CommissionFee
{
    private $privateWithdrawalRule;
    private $businessWithdrawalRule;
    private $depositRule;
    protected $currentExchangeRate;

    public function __construct(ExchangeRate $currentExchangeRate)
    {
        $this->privateWithdrawalRule = [
            'fee' => config('constants.PRIVATE_WITHDRAWL_FEE'),
            'free_amount' => config('constants.PRIVATE_WITHDRAWL_FREE_AMOUNT'),
            'free_operations' => config('constants.PRIVATE_WITHDRAWL_FREE_OPERATIONS'),
        ];
        $this->businessWithdrawalRule = [
            'fee' => config('constants.BUSINESS_WITHDRAWL_FEE'),
        ];
        $this->depositRule = [
            'fee' => config('constants.DEPOSIT_FEE'),
        ];
        $this->currentExchangeRate = $currentExchangeRate;
    }

    public function getCommissionFee($data)
    {
        // Loop through operations and calculate commission fees
        $fees = [];
        $exceeds = [];
        foreach ($data as $operation) {
            $date = $operation[0];
            $userId = $operation[1];
            $userType = $operation[2];
            $operationType = $operation[3];
            $amount = $operation[4];
            $currency = $operation[5];

            // Convert amount to EUR if necessary
            if ($currency !== CurrencyName::EURO) {
                $eurAmount = $amount / $this->currentExchangeRate->getExchangeRate()['rates'][$currency];
            } else {
                $eurAmount = $amount;
            }

            // Calculate commission fee
            switch ($operationType) {
                case UserOperation::DEPOSIT:
                    $fee = $eurAmount * $this->depositRule['fee'];
                    array_push($fees, round($fee, 2));
                    break;
                case UserOperation::WITHDRAW:
                    if ($userType === UserType::PRIVATE) {
                        // Check if operation is free of charge
                        $weekStart = date('Y-m-d', strtotime('last monday', strtotime($date)));
                        $weekEnd = date('Y-m-d', strtotime('next sunday', strtotime($date)));

                        $weekOperations = array_filter(
                            $data,
                            function ($op) use ($userId, $userType, $weekStart, $weekEnd) {
                                return $op[1] === $userId && $op[2] ===
                                    $userType && $op[3] === UserOperation::WITHDRAW
                                    && $op[0] >= $weekStart && $op[0] <= $weekEnd;
                            }
                        );
                        if (
                            count($weekOperations) <= $this->privateWithdrawalRule['free_operations']
                            && $eurAmount <= $this->privateWithdrawalRule
                            && array_sum(array_column($weekOperations, 4)) <=
                            $this->privateWithdrawalRule['free_amount']
                        ) {
                            $fee = 0;
                            array_push($fees, $fee);
                        } elseif (
                            $eurAmount >= $this->privateWithdrawalRule['free_operations']
                            && !in_array($userId, $exceeds)
                        ) {
                            $fee = max(
                                ($eurAmount -
                                    $this->privateWithdrawalRule['free_amount']) * $this->privateWithdrawalRule['fee'],
                                0
                            );
                            array_push($fees, $currency !== CurrencyName::EURO ?
                                round(
                                    $fee * $this->currentExchangeRate->getExchangeRate()['rates'][$currency],
                                    2
                                ) : round($fee, 2));
                            array_push($exceeds, $userId);
                        } else {
                            $fee = max($eurAmount * $this->privateWithdrawalRule['fee'], 0);
                            array_push($fees, $currency !== CurrencyName::EURO ?
                                round(
                                    $fee * $this->currentExchangeRate->getExchangeRate()['rates'][$currency],
                                    2
                                ) : round($fee, 2));
                        }
                    } elseif ($userType === UserType::BUSINESS) {
                        $fee = $this->businessWithdrawalRule['fee'] * $eurAmount;
                        array_push($fees, $currency !== CurrencyName::EURO ?
                            round(
                                $fee * $this->currentExchangeRate->getExchangeRate()['rates'][$currency],
                                2
                            ) : round($fee, 2));
                    }
            }
        }

        return $fees;
    }
}
