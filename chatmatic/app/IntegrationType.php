<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\IntegrationType
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationType query()
 * @mixin \Eloquent
 * @property int $uid
 * @property string $name
 * @property string $slug
 * @property string $parameters
 * @property bool $active
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Integration[] $integrations
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationType whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationType whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationType whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\IntegrationType whereUid($value)
 * @property-read int|null $integrations_count
 */
class IntegrationType extends Model
{
    public $timestamps      = false;
    protected $table        = 'integration_types';
    protected $primaryKey   = 'uid';
    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function integrations()
    {
        return $this->hasMany(Integration::class, 'integration_type_uid');
    }

}
