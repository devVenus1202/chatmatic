<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TriggerConfTrigger
 * properties int type would be bigint or string to avoid future errors
 *
 * @property int $uid
 * @property string $message
 * @property int $comments
 * @property int $message_sent
 * @property int $post_uid
 * @property-read \App\WorkflowTrigger $workflowTrigger
 * @property-read \App\Posts $post
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfTrigger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfTrigger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfTrigger query()
 * @mixin \Eloquent
 */
class TriggerConfTrigger extends Model
{
    public $timestamps      = false;

    protected $primaryKey   = 'uid';
    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTrigger()
    {
        return $this->belongsTo(WorkflowTrigger::class, 'workflow_trigger_uid', 'uid');
    }
    
    /**
     * @param $request_options
     * @param $workflow_trigger
     * @return mixed
     * @throws \Exception
     */
    public static function updateOrCreate($request_options, $workflow_trigger)
    {
        $response['success']                = 0;
        $response['error']                  = 0;
        $response['error_msg']              = '';
        $response['workflow_step_items']    = null;

        // Extract the needed options 
        $post_uid                    = $request_options['post_uid'];
        $config_uid                  = $request_options['uid'] ?? null ;
        $active                      = $request_options['active'] ?? null;

        if ( isset($config_uid) )
        {
            // Update the post trigger
            $post_trigger               = $workflow_trigger->postTrigger()->first();
        }
        else
        {
            // Create a new post trigger
            $post_trigger               = new self;
        }

        // Let's validate the post exist on our database
        $post = $workflow_trigger->page->posts()->where('uid',$post_uid)->first();
        if ( ! isset($post) )
        {
            // post uid is has not a related page post
            $response['error'] = 1;
            $response['error_msg'] = 'There is not a post from that page for the post uid '.$post_uid;

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }
                
        // Once created the workflow trigger, let's create the workflow trigger
        $post_trigger->message                          = '';
        $post_trigger->comments                         = 0;
        $post_trigger->messages_sent                    = 0;
        $post_trigger->post_uid                         = $post_uid;
        $post_trigger->active                           = $active ?? 1;
        $post_trigger->workflow_trigger_uid             = $workflow_trigger->uid;


        $post_trigger_saved = $post_trigger->save();
        if( ! $post_trigger_saved)
        {
            // Saving the keyword config trigger failed
            $response['error'] = 1;
            $response['error_msg'] = 'Error saving the Post config trigger';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        return $response;
    }
}
