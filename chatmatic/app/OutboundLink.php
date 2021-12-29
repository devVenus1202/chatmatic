<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\OutboundLink
 *
 * @property int $uid
 * @property int $page_uid
 * @property int $workflow_uid
 * @property int $workflow_step_uid
 * @property int|null $workflow_step_item_map_uid
 * @property int|null $workflow_step_item_image_uid
 * @property string $url
 * @property string $slug
 * @property int $redirect_count
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \App\Page $page
 * @property-read \App\Workflow $workflow
 * @property-read \App\WorkflowStep $workflowStep
 * @property-read \App\WorkflowStepItem $workflowStepItem
 * @property-read \App\WorkflowStepItemImage|null $workflowStepItemImage
 * @property-read \App\WorkflowStepItemMap|null $workflowStepItemMap
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereRedirectCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereWorkflowStepItemImageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereWorkflowStepItemMapUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereWorkflowStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereWorkflowUid($value)
 * @mixin \Eloquent
 * @property int $workflow_step_item_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutboundLink whereWorkflowStepItemUid($value)
 */
class OutboundLink extends Model
{
    public $timestamps = false;

    protected $table        = 'outbound_links';
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStepItemMap()
    {
        return $this->belongsTo(WorkflowStepItemMap::class, 'workflow_step_item_map_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStepItemImage()
    {
        return $this->belongsTo(WorkflowStepItemImage::class, 'workflow_step_item_image_uid', 'uid');
    }

    /**
     * @param $url
     * @param null $workflow_step_item_map_uid
     * @param null $workflow_step_item_image_uid
     * @return OutboundLink
     * @throws \Exception
     */
    public static function findOrCreateNewLink($url, $workflow_step_item_map_uid = null, $workflow_step_item_image_uid = null, $workflow_step_item_map = null)
    {
        if($workflow_step_item_map_uid !== null)
        {
            // Confirm there isn't already a link with this url for the same item_map
            $link = OutboundLink::where('url', $url)->where('workflow_step_item_map_uid', $workflow_step_item_map_uid)->first();
            if($link)
            {
                return $link;
            }
        }

        if($workflow_step_item_image_uid !== null)
        {
            // Confirm there isn't already a link with this url for the same item_image
            $link = OutboundLink::where('url', $url)->where('workflow_step_item_image_uid', $workflow_step_item_image_uid)->first();
            if($link)
            {
                return $link;
            }
        }

        if($workflow_step_item_map)
        {
            $parent_object = $workflow_step_item_map;
        }
        else
        {
            $parent_object = null;
        }

        // There's no existing OutboundLink - let's create one.
        // Find the parent of this link to use their uids

        if($parent_object === null && $workflow_step_item_map_uid)
        {
            $parent_object = WorkflowStepItemMap::find($workflow_step_item_map_uid);
        }
        elseif($parent_object === null && $workflow_step_item_image_uid)
        {
            $parent_object = WorkflowStepItemImage::find($workflow_step_item_image_uid);
        }

        // Throw a warning if no parent object found
        if($parent_object === null)
        {
            throw new \Exception('OutboundLink parent object not found.');
        }

        $link                                   = new OutboundLink();
        $link->url                              = $url;
        $link->page_uid                         = $parent_object->page_uid;
        $link->workflow_uid                     = $parent_object->workflow_uid;
        $link->workflow_step_uid                = $parent_object->workflow_step_uid;
        $link->workflow_step_item_uid           = $parent_object->workflow_step_item_uid;
        $link->workflow_step_item_map_uid       = $workflow_step_item_map_uid;
        $link->workflow_step_item_image_uid     = $workflow_step_item_image_uid;
        $link->slug                             = \Hashids::encode(time() + random_int(100000, 9999999));
        $link->save();

        return $link;
    }

    /**
     * Helper method to reset the OutboundLinks associated with all images and buttons to null
     */
    public static function resetOutboundUrls()
    {
        foreach (\App\WorkflowStepItemImage::all() as $image)
        {
            $image->outbound_link_uid = null;
            $image->save();
        }

        foreach (\App\WorkflowStepItemMap::all() as $button)
        {
            $button->outbound_link_uid = null;
            $button->save();
        }

        foreach(\App\OutboundLink::all() as $link)
        {
            $link->delete();
        }
    }

    /**
     * Helper method to populate OutboundLink associations on all images and buttons
     *
     * @throws \Exception
     */
    public static function populateOutboundUrls()
    {
        $images = \App\WorkflowStepItemImage::whereNull('outbound_link_uid')->get();

        // Populate WorkflowStepItemImage OutboundLink records
        foreach($images as $image)
        {
            if($image->redirect_url !== '')
            {
                // Generate a link
                $link = \App\OutboundLink::findOrCreateNewLink($image->redirect_url, null, $image->uid);
                $image->outbound_link_uid = $link->uid;
                $image->save();
            }
        }

        $buttons = \App\WorkflowStepItemMap::where('map_action', 'web_url')->whereNull('outbound_link_uid')->get();

        // Populate WorkflowStepItemMap OutboundLink records
        foreach($buttons as $button)
        {
            // Generate a link
            $link = \App\OutboundLink::findOrCreateNewLink($button->map_action_text, $button->uid);
            $button->outbound_link_uid = $link->uid;
            $button->save();
        }
    }
}
