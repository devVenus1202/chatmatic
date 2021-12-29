<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CustomFieldResponse
 *
 * @property int $uid
 * @property string $response
 * @property int $custom_field_uid
 * @property int $subscriber_psid
 * @property-read \App\CustomField $customField
 * @property-read \App\Subscriber $subscriber
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomFieldResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomFieldResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomFieldResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomFieldResponse whereCustomFieldUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomFieldResponse whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomFieldResponse whereSubscriberPsid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomFieldResponse whereUid($value)
 * @mixin \Eloquent
 */
class CustomFieldResponse extends Model
{
    protected $table        = 'custom_field_responses';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    public $timestamps      = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customField()
    {
        return $this->belongsTo(CustomField::class, 'custom_field_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_psid', 'user_psid');
    }
}
