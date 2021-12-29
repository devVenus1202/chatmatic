<?php

namespace App;

use App\Chatmatic\APIHelpers\FacebookGraphAPIHelper;
use App\Traits\WorkflowStepItemMediaTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowStepItemImage
 *
 * @property int $uid
 * @property int $workflow_step_item_uid
 * @property int $image_order
 * @property string $image_url
 * @property string $redirect_url
 * @property string|null $image_title
 * @property string|null $image_subtitle
 * @property int $page_uid
 * @property int $workflow_uid
 * @property int $workflow_step_uid
 * @property-read \App\Page $page
 * @property-read \App\Workflow $workflow
 * @property-read \App\WorkflowStep $workflowStep
 * @property-read \App\WorkflowStepItem $workflowStepItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereImageOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereImageSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereImageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereWorkflowStepItemUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereWorkflowStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereWorkflowUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage query()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Tag[] $tags
 * @property-read \App\WorkflowStepItemMap $workflowStepItemMap
 * @property-read \App\WorkflowStepItemMap $workflowStepItemMaps
 * @property string|null $reference_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereReferenceId($value)
 * @property int $clicks
 * @property int|null $outbound_link_uid
 * @property string|null $fb_attachment_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereFbAttachmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemImage whereOutboundLinkUid($value)
 * @property-read int|null $tags_count
 * @property-read int|null $workflow_step_item_maps_count
 */
class WorkflowStepItemImage extends Model
{
    use WorkflowStepItemMediaTrait;

    protected $table        = 'workflow_step_item_images';
    protected $primaryKey   = 'uid';

    public $timestamps      = false;

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
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStepItem()
    {
        return $this->belongsTo(WorkflowStepItem::class, 'workflow_step_item_uid', 'uid');
    }

    /**
     * Relationship for buttons associated to this image, only used on carousel workflow step items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowStepItemMaps()
    {
        return $this->hasMany(WorkflowStepItemMap::class, 'workflow_step_item_image_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * @return bool
     */
    public function getFacebookAttachmentId()
    {
        $fb_helper = new FacebookGraphAPIHelper(
            config('chatmatic.app_id'),
            config('chatmatic.app_secret')
        );

        $response = $fb_helper->getMediaObjectAttachmentId($this->page->facebook_connected_access_token, $this->image_url, 'image');
        $attachment_id = $response['attachment_id'];

        $this->fb_attachment_id = $attachment_id;
        return $this->save();
    }

    /**
     * This should only work if the workflow_step_item is a carousel
     *
     * @return array
     */
    public function buildButtonsArray()
    {
        $buttons = [];
        if($this->workflowStepItem->item_type === 'carousel')
        {
            if($this->workflowStepItemMaps()->count())
            {
                // Set the buttons
                foreach($this->workflowStepItemMaps()->orderBy('uid', 'asc')->get() as $workflow_step_item_map)
                {
                    $button = [
                        'type'    => $workflow_step_item_map->map_action,
                        'title'   => str_limit($workflow_step_item_map->map_text, 20),
                    ];

                    switch($workflow_step_item_map->map_action)
                    {
                        case 'web_url':
                            $button['url'] = $workflow_step_item_map->map_action_text;
                            break;

                        case 'postback':
                            $button['payload'] = $workflow_step_item_map->map_action_text;
                            break;
                    }

                    $buttons[] = $button;
                }
            }
        }

        return $buttons;
    }
}
