<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * App\ZapierEventLog
 *
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierEventLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierEventLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierEventLog query()
 * @mixin \Eloquent
 * @property int $uid
 * @property int $page_uid
 * @property string $event_type
 * @property string $action
 * @property string|null $payload
 * @property string|null $response
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierEventLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierEventLog whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierEventLog whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierEventLog wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierEventLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierEventLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierEventLog whereUid($value)
 */
class ZapierEventLog extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = null;

    protected $table        = 'zapier_event_logs';
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
     * @param Page $page
     * @param Request $request
     * @param $event_type
     * @param $event_action
     * @return array|Model
     */
    public static function createPageEventRecord(Page $page, Request $request, $event_type, $event_action)
    {
        $zapier_event = [
            'action'        => $event_action,
            'payload'       => json_encode($request->all()),
            'event_type'    => $event_type
        ];

        $zapier_event = $page->zapierEventLogs()->create($zapier_event);

        return $zapier_event;
    }
}
