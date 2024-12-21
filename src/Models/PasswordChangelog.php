<?php

namespace Beliven\PasswordExpiry\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PasswordChangelog extends Model
{
    protected $table = 'model_password_changes';

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function scopeByModel(Builder $query, Model $model): Builder
    {
        $id = $model->getAttribute('id');

        return $query
            ->where('model_type', $model::class)
            ->where('model_id', $id);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeUpcomingExpiration(Builder $query): Builder
    {
        $days = config('password-expiry.days_to_notify_expiration');

        $expiresAt = now()->addDays($days);

        return $query->whereDate('expires_at', $expiresAt);
    }
}
