<?php
namespace Mustafaaycll\PhpChat\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'username';
    protected $keyType = 'string';
    protected $fillable = ['username'];

    public $incrementing = false;
    public $timestamps = false;

    public function messagesSent(): HasMany
    {
        return $this->hasMany(Message::class, 'sent_by', 'username');
    }

    public function groupsCreated(): HasMany
    {
        return $this->hasMany(Chat::class, 'created_by', 'username');
    }

    public function chatsAttended(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'group_users', 'user_id', 'group_id');
    }
}