<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    protected $table = 'people';
    protected $primaryKey = 'person_id';

    public $timestamps = false;

    protected $fillable = [
        'first_names',
        'last_names',
        'document_type',
        'document_number',
        'phone',
        'address',
        'email',
        'gender',
        'birth_date'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'person_id', 'person_id');
    }
}