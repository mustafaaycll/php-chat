<?php
namespace Mustafaaycll\PhpChat\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'messages';
    protected $fillable = ['sent_by', 'sent_to', 'sent_at', 'content'];
    protected $casts = [
        'sent_at' => 'integer',
        'content' => 'string',
    ];

    public $timestamps = false;

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by', 'username');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'sent_to');
    }
}