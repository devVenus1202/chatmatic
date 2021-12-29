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
 * @property-read \App\User $chatmatic_buyer
 * @property-read \App\User $chatmatic_seller
 * @property-read \App\Page $page
 * @property-read \App\WorkflowTemplate $template
 * @method static \Illuminate\Database\Eloquent\Builder|StripePurchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StripePurchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StripePurchase query()
 * @mixin \Eloquent
 */
class StripePurchase extends Model
{

    public $timestamps      = false;

    protected $table        = 'stripe_purchases';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }

   
   public function chatmatic_buyer()
   {
        return $this->belongsTo(User::class, 'chatmatic_buyer_uid','uid');
   } 

   public function chatmatic_seller()
   {
        return $this->belongsTo(User::class, 'chatmatic_seller_uid','uid');
   } 

   public function template()
   {
        return $this->belongsTo(WorkflowTemplate::class,'template_uid','uid');
   }

   /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
  public function page()
  {
       return $this->belongsTo(Page::class, 'page_uid', 'uid');
  }

}
