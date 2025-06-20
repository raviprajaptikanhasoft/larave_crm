<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $fillable = ['name', 'type', 'is_required', 'show_on_table'];

    public function values() {
        return $this->hasMany(ContactCustomFieldValue::class);
    }
}
