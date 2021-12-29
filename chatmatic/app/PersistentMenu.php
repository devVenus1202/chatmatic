<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PersistentMenu
 *
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenu query()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PersistentMenuItem[] $menuItems
 * @property int $uid
 * @property string $locale
 * @property bool $composer_input_disable
 * @property int $page_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenu whereComposerInputDisable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenu whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenu wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PersistentMenu whereUid($value)
 * @property-read int|null $menu_items_count
 */
class PersistentMenu extends Model
{
    public $timestamps      = false;

    protected $table        = 'persistent_menu';
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function menuItems()
    {
        return $this->hasMany(PersistentMenuItem::class, 'persistent_menu_uid', 'uid');
    }
}
