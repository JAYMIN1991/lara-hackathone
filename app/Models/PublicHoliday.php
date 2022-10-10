<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends BaseModel
{
    protected $table = 'public_holidays';

    protected $primaryKey = 'holiday_id';
}
