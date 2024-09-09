<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Task extends Model
{
    use HasFactory,SoftDeletes;
    

    protected $fillable = ['title', 'description', 'priority', 'status', 'due_date', 'assigned_to'];
    protected $table = 'tasks'; // تحديد اسم الجدول
    protected $primaryKey = 'id'; // المفتاح الأساسي

    // Accessors and Mutators for due_date
    protected $dates = ['due_date'];

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = Carbon::createFromFormat('d-m-Y H:i', $value);
    }

    public function getDueDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i');
    }

    // Query Scopes
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // علاقة Many-to-One: المهمة تعود لمستخدم معين
    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
