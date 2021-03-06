<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    
    protected $table="notes";
    protected $fillable = ['title','description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function label()
    {
        return $this->belongsTo(Label::class);
    }
    public function labelnote()
    {
        return $this->belongsTo(LabelNote::class);
    }
   
}
