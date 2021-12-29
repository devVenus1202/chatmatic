<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\ApiErrorLog
 *
 * @property int $uid
 * @property int $page_uid
 * @property int|null $workflow_uid
 * @property bool $resolved
 * @property string $error_msg
 * @property string|null $request
 * @property string|null $response
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @property \Illuminate\Support\Carbon $updated_at_utc
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog whereErrorMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog whereRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog whereResolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ApiErrorLog whereWorkflowUid($value)
 * @mixin \Eloquent
 * @property bool $email
 * @method static \Illuminate\Database\Eloquent\Builder|ApiErrorLog whereEmail($value)
 */
class ApiErrorLog extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'api_error_log';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid', 'uid');
    }
}
