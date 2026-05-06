<?php


namespace App\Models;

use App\Models\Room;
use App\Models\Facility;
use App\Models\Favorite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kost extends Model
{
    use HasFactory;

    protected $with = ['rooms', 'owner', 'facilities'];

    protected $fillable = [
        'owner_id', 'name', 'description', 'address', 'city', 'province', 'latitude', 'longitude', 'type',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'kost_facilities');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}