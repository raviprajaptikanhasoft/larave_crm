<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'gender', 'profile_image', 'additional_file'];

    public function customFields() {
        return $this->hasMany(ContactCustomFieldValue::class);
    }
}
