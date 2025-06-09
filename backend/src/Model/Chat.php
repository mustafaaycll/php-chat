<?php
namespace Mustafaaycll\PhpChat\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $table = 'groups';
    protected $fillable = ['name', 'created_by'];
    protected $casts = [
        'name' => 'string',
    ];

    public $timestamps = false;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sent_to');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_users', 'group_id', 'user_id');
    }
}