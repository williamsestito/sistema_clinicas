<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulePeriodDay extends Model
{
    use HasFactory;

    protected $table = 'schedule_period_days';

    protected $fillable = [
        'tenant_id',
        'professional_id',
        'period_id',
        'weekday',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'duration',
        'available',
    ];

    /**
     * --------------------------------------------------------------------------
     * CASTS — configurados corretamente
     * --------------------------------------------------------------------------
     *
     * ✔ weekday → integer
     * ✔ duration → integer
     * ✔ available → boolean
     *
     * ⚠ IMPORTANTE:
     *  Não usar "datetime:H:i" em colunas do tipo TIME.
     *  Laravel não lida corretamente com isso — ele converte para 1970-01-01,
     *  causando bugs de comparação e loops infinitos.
     *
     * Portanto, os campos TIME continuam strings puras ("HH:MM:SS") e só são
     * convertidos manualmente em Carbon dentro dos controllers.
     * --------------------------------------------------------------------------
     */
    protected $casts = [
        'weekday'      => 'integer',
        'duration'     => 'integer',
        'available'    => 'boolean',

        // TIME → string
        'start_time'   => 'string',
        'end_time'     => 'string',
        'break_start'  => 'string',
        'break_end'    => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    public function period()
    {
        return $this->belongsTo(SchedulePeriod::class, 'period_id');
    }
}
