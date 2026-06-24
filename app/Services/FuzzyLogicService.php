<?php

namespace App\Services;

class FuzzyLogicService
{
    public function analyze(float $flowRate): array
    {
        // No water movement detected.
        if ($flowRate <= 0.05) {
            return [
                'status' => 'Offline',
                'condition' => 'No Water Flow',
                'recommendation' => 'No water movement was detected. Check the water source, canal flow, and propeller sensor position.',
                'memberships' => [
                    'low' => 0.0,
                    'normal' => 0.0,
                    'high' => 0.0,
                ],
                'score' => 0.0,
            ];
        }

        /*
         * Fuzzy membership functions.
         *
         * Low flow:    fully low until 7 L/min, gradually decreases to 0 at 12 L/min
         * Normal flow: gradually starts at 8 L/min, strongest from 11–15 L/min,
         *              then gradually decreases to 0 at 18 L/min
         * High flow:   gradually starts at 15 L/min and becomes fully high at 20 L/min
         *
         * These are temporary prototype ranges.
         * We will revise them after actual propeller calibration and NIA validation.
         */
        $low = $this->leftShoulder($flowRate, 7.0, 12.0);
        $normal = $this->trapezoid($flowRate, 8.0, 11.0, 15.0, 18.0);
        $high = $this->rightShoulder($flowRate, 15.0, 20.0);

        /*
         * Simplified fuzzy inference and defuzzification.
         *
         * Low rule output    = 25
         * Normal rule output = 50
         * High rule output   = 75
         */
        $weightTotal = $low + $normal + $high;

        $score = $weightTotal > 0
            ? (($low * 25) + ($normal * 50) + ($high * 75)) / $weightTotal
            : 50;

        if ($score < 36) {
            $status = 'Low';
            $condition = 'Low Discharge';
            $recommendation = 'Low water discharge was detected. Inspect the water source, canal flow, and possible blockage before adjusting water distribution.';
        } elseif ($score < 47) {
            $status = 'Low';
            $condition = 'Slightly Low Discharge';
            $recommendation = 'Water discharge is slightly below the desired range. Continue monitoring and inspect the canal flow before making adjustments.';
        } elseif ($score <= 54) {
            $status = 'Normal';
            $condition = 'Normal Discharge';
            $recommendation = 'Water discharge is within the normal range. Continue regular irrigation monitoring and maintain the current water distribution.';
        } elseif ($score <= 64) {
            $status = 'High';
            $condition = 'Slightly High Discharge';
            $recommendation = 'Water discharge is approaching a high level. Monitor the canal and prepare to reduce water release if the flow continues to increase.';
        } else {
            $status = 'High';
            $condition = 'High Discharge';
            $recommendation = 'High water discharge was detected. Inspect for possible overflow or water wastage and reduce water release when necessary.';
        }

        return [
            'status' => $status,
            'condition' => $condition,
            'recommendation' => $recommendation,
            'memberships' => [
                'low' => round($low, 2),
                'normal' => round($normal, 2),
                'high' => round($high, 2),
            ],
            'score' => round($score, 2),
        ];
    }

    private function leftShoulder(float $value, float $fullUntil, float $zeroAt): float
    {
        if ($value <= $fullUntil) {
            return 1.0;
        }

        if ($value >= $zeroAt) {
            return 0.0;
        }

        return ($zeroAt - $value) / ($zeroAt - $fullUntil);
    }

    private function rightShoulder(float $value, float $zeroUntil, float $fullAt): float
    {
        if ($value <= $zeroUntil) {
            return 0.0;
        }

        if ($value >= $fullAt) {
            return 1.0;
        }

        return ($value - $zeroUntil) / ($fullAt - $zeroUntil);
    }

    private function trapezoid(
        float $value,
        float $start,
        float $fullStart,
        float $fullEnd,
        float $end
    ): float {
        if ($value <= $start || $value >= $end) {
            return 0.0;
        }

        if ($value >= $fullStart && $value <= $fullEnd) {
            return 1.0;
        }

        if ($value < $fullStart) {
            return ($value - $start) / ($fullStart - $start);
        }

        return ($end - $value) / ($end - $fullEnd);
    }
}
