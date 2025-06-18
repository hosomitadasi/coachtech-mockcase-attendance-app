<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Application;

class ApplicationBreak extends Model
{
    protected $table = 'application_breaks';
    protected $fillable = ['application_id','break_in','break_out'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
