<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    // History.php

    public function scopeWithinPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('playback_date', [$startDate, $endDate]);
    }

}
