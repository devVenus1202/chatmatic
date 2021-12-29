<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * App\User
 *
 * @property int $uid
 * @property float $balance
 * @property string $facebook_email
 * @property int $stripe_subscription
 * @property int $chatmatic_user_id
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|SmsHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsHistory query()
 * @mixin \Eloquent
 */
class SmsHistory extends Model
{
    public $timestamps = false;

    protected $table        = 'sms_history';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

   
   public function page()
   {
        return $this->belongsTo(Page::class, 'page_uid','uid');
   } 

}
