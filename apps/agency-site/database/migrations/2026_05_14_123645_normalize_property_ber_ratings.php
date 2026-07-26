<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groups = [
            'A' => ['a', 'A', 'a1', 'a2', 'a3', 'A1', 'A2', 'A3'],
            'B' => ['b', 'B', 'b1', 'b2', 'b3', 'B1', 'B2', 'B3'],
            'C' => ['c', 'C', 'c1', 'c2', 'c3', 'C1', 'C2', 'C3'],
            'D' => ['d', 'D', 'd1', 'd2', 'D1', 'D2'],
            'E' => ['e', 'E', 'e1', 'e2', 'E1', 'E2'],
            'F' => ['f', 'F'],
            'G' => ['g', 'G'],
            'Exempt' => ['exempt', 'EXEMPT'],
        ];

        foreach ($groups as $group => $ratings) {
            DB::table('properties')
                ->whereIn('ber_rating', $ratings)
                ->update(['ber_rating' => $group]);
        }
    }

    public function down(): void
    {
        //
    }
};
