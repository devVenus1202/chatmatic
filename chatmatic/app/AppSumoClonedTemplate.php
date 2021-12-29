<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\ApiErrorLog
 *
 * @property int $uid
 * @property string $error_msg
 */
class AppSumoClonedTemplate extends Model
{
    public $timestamps      = false;
    
    const CREATED_AT        = 'created_at_utc';

    protected $table        = 'app_sumo_cloned_templates';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'page_uid', 'uid');
    }

}
