<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\IntegrationRecord
 *
 * @property int $uid
 * @property int $integration_uid
 * @property int $integration_type_uid
 * @property int $page_uid
 * @property bool $success
 * @property string $payload
 * @property string $response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Integration $integration
 * @property-read \App\IntegrationType $integrationType
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord whereIntegrationTypeUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord whereIntegrationUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @property \Illuminate\Support\Carbon $updated_at_utc
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationRecord whereUpdatedAtUtc($value)
 */
class IntegrationRecord extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'integration_records';
    protected $primaryKey   = 'uid';
    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function integrationType()
    {
        return $this->belongsTo(IntegrationType::class, 'integration_type_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function integration()
    {
        return $this->belongsTo(Integration::class, 'integration_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid');
    }
}
