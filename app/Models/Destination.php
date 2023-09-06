<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\DestinationController;

class Destination extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'title',
        'location',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    static function search($query){
    $results = Destination::where('title', 'LIKE', "%$query%")
                          ->orWhere('location', 'LIKE', "%$query%")
                          ->get();
    return $results;
                        }
}
