<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Trigger
 *
 * @property int $uid
 * @property int $page_uid
 * @property int|null $post_uid
 * @property string $inclusion_keywords
 * @property string $exclusion_keywords
 * @property string $message
 * @property int $comments
 * @property int $exclusion_match_count
 * @property int $inclusion_match_count
 * @property int $inclusion_non_match_count
 * @property int $acceptable_and_no_inclusion_count
 * @property int $messages_sent
 * @property int $messages_opened
 * @property \Carbon\Carbon $created_at_utc
 * @property \Carbon\Carbon $updated_at_utc
 * @property int $active
 * @property-read \App\Page $page
 * @property-read \App\Post|null $post
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereAcceptableAndNoInclusionCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereExclusionKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereExclusionMatchCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereInclusionKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereInclusionMatchCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereInclusionNonMatchCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereMessagesOpened($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereMessagesSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger wherePostUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereUpdatedAtUtc($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger query()
 * @property int|null $workflow_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Trigger whereWorkflowUid($value)
 */
class Trigger extends Model
{
    public $timestamps        = false;
    //const CREATED_AT        = 'created_at_utc';
    //const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'trigger_conf_triggers';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }

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
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_uid', 'uid');
    }

    /**
     * Generate a public id
     *
     * @return string
     */
    public static function generatePublicId()
    {
        $characters                 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $last_character_position    = strlen($characters) - 1;
        $now                        = gmdate('YmdHis');
        $new_public_id              = $characters[mt_rand(0, $last_character_position)];
        for ($i = strlen($now) - 1; $i >= 0; $i--)
        {
            $new_public_id .= $now[$i] . $characters[mt_rand(0, $last_character_position)];
        }

        return $new_public_id;
    }
}
