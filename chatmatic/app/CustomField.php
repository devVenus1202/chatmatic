<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CustomField
 *
 * @property int $uid
 * @property string $field_name
 * @property string $validation_type
 * @property int $page_uid
 * @property string $tag
 * @property string $tag_type
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField whereFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField whereTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField whereTagType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField whereValidationType($value)
 * @mixin \Eloquent
 * @property string $merge_tag
 * @property string $custom_field_type
 * @property string|null $default_value
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField whereCustomFieldType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField whereDefaultValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField whereMergeTag($value)
 * @property bool|null $archived
 * @property string|null $archived_at_utc
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CustomFieldResponse[] $customFieldResponses
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField whereArchived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomField whereArchivedAtUtc($value)
 * @property-read int|null $custom_field_responses_count
 */
class CustomField extends Model
{
    protected $table        = 'custom_fields';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    public $timestamps      = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customFieldResponses()
    {
        return $this->hasMany(CustomFieldResponse::class, 'custom_field_uid', 'uid');
    }
}
