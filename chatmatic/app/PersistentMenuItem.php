<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\PersistentMenuItem
 *
 * @property int $uid
 * @property string $title
 * @property string $type
 * @property string|null $payload
 * @property string|null $url
 * @property int|null $parent_menu_uid
 * @property int $persistent_menu_uid
 * @property-read \App\PersistentMenu|null $parentMenu
 * @property-read \App\PersistentMenu $persistentMenu
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenuItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenuItem whereParentMenuUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenuItem wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenuItem wherePersistentMenuUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenuItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenuItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenuItem whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenuItem whereUrl($value)
 * @mixin \Eloquent
 * @property bool $branded
 * @method static \Illuminate\Database\Eloquent\Builder|PersistentMenuItem whereBranded($value)
 */
class PersistentMenuItem extends Model
{
    public $timestamps      = false;

    protected $table        = 'persistent_menu_items';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function persistentMenu()
    {
        return $this->belongsTo(PersistentMenu::class, 'persistent_menu_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parentMenu()
    {
        return $this->belongsTo(PersistentMenu::class, 'parent_menu_uid', 'uid');
    }
}
