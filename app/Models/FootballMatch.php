<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FootballMatch extends Model
{
    protected $table = 'matches';
    
    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'week',
        'home_score',
        'away_score',
        'is_played',
        'played_at'
    ];

    protected $casts = [
        'is_played' => 'boolean',
        'played_at' => 'datetime'
    ];

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }
}
