<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'class_id',
        'section_id',
        'status',
        'image'
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function scopeSearch(Builder $query, Request $request)
    {
        return $query->where(function ($query) use ($request) {
            return $query->when($request->search, function ($query) use ($request) {
                return $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })->when($request->class_id, function ($query) use ($request) {
                return $query->where('class_id', $request->class_id);
            })->when($request->has('sort'), function ($query) use ($request) {
                $sortColumn = $request->input('sort');
                $sortDirection = $request->input('direction', 'asc');

                if (in_array($sortColumn, ['name', 'email'])) {
                    $query->orderBy($sortColumn, $sortDirection);
                }
            })->when($request->has('status'), function ($query) use ($request) {
                $status = $request->input('status');
                if (in_array($status, [0, 1])) {
                    $query->where('status', $status);
                }
            });
        });
    }
}
