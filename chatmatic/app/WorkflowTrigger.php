<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\WorkflowTrigger
 *
 * @property int $uid
 * @property string type
 * @property string $name
 * @property int $messages_delivered
 * @property int $messages_read
 * @property int $messages_clicked
 * @property int conversions
 * @property bool|null $archived
 * @property string|null $archived_at_utc
 * @property string $created_at_utc
 * @property string $updated_at_utc
 * @property int $page_uid
 * @property int $workflow_uid
 * @property-read \App\TriggerConfBroadcast|null $broadcast
 * @property-read \App\TriggerConfButton|null $button
 * @property-read \App\TriggerConfKeyword|null $keyword
 * @property-read \App\TriggerConfLandingPage|null $landingPage
 * @property-read \App\TriggerConfMdotme|null $mdotme
 * @property-read \App\Page $page
 * @property-read \App\TriggerConfTrigger|null $postTrigger
 * @property-read \App\Workflow $workflow
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTrigger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTrigger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTrigger query()
 * @mixin \Eloquent
 */
class WorkflowTrigger extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'workflow_triggers';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    protected $dates = [
        'archived_at_utc'
    ];

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
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
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function broadcast()
    {
        return $this->hasOne(TriggerConfBroadcast::class, 'workflow_trigger_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function button()
    {
        return $this->hasOne(TriggerConfButton::class, 'workflow_trigger_uid', 'uid');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function keyword()
    {
        return $this->hasOne(TriggerConfKeyword::class, 'workflow_trigger_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function landingPage()
    {
        return $this->hasOne(TriggerConfLandingPage::class, 'workflow_trigger_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mdotme()
    {
        return $this->hasOne(TriggerConfMdotme::class, 'workflow_trigger_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function postTrigger()
    {
        return $this->hasOne(TriggerConfTrigger::class, 'workflow_trigger_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function chatWidget()
    {
        return $this->hasOne(TriggerConfChatWidget::class, 'workflow_trigger_uid', 'uid');
    }

    /**
     * Archive this Workflow
     *
     * @return bool
     */
    public function archive()
    {
        $this->archived = true;
        return $this->save();
    }

    /**
     * Un-archive this Workflow
     *
     * @return bool
     */
    public function unArchive()
    {
        $this->archived = false;
        return $this->save();
    }

    /**
     * Validate the request
     * 
     * @param request
     * @return mixed
     * @throws \Exception
     */
    public static function validateApiRequest($request, $page)
    {
        $response['success']                = 0;
        $response['error']                  = 0;
        $response['error_msg']              = '';

        // Extract request vars
        $trigger_type           = $request->get('type');
        $trigger_name           = $request->get('trigger_name');
        $workflow_uid           = $request->get('workflow_uid');

        // Let's check we have the needed data

        // Validate the existence of the name
        if ( ! isset($trigger_name))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'A trigger name is needed.';

            return $response;
        }
        // Validate the trigger name length
        if(mb_strlen($trigger_name) > 45){
            $response['error'] = 1;
            $response['error_msg'] = 'Flow Trigger name is too long, please limit to 45 characters or less. Current character count: '.mb_strlen($trigger_name);

            return $response;
        }

        // Validate the existence of a workflow uid
        if ( ! isset($workflow_uid))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'There is not a related flow';

            return $response;
        }
        $workflow = $page->workflows()->where('uid',$workflow_uid)->first();
        if(! isset($workflow))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'There is not any flow with the uid '.$workflow_uid;

            return $response;
        }

        // If everything is ok, let's return the workflow
        $response['workflow'] = $workflow;

        return $response;
    }

    /**
     * Trigger broadcast on local pipeline API
     */
    public function trigger()
    {
        $response = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
        ];

        $base_url       = getenv('PIPELINE_INTERNAL_BASE_URL');
        $broadcast_url  = $base_url.'/broadcasts';

        $request_vars   = [
            'broadcast_action' => 'start',
            'broadcast_uid'     => $this->uid
        ];

        $curl = curl_init($broadcast_url);
        if ($curl === false) {
            $error_message          = 'Unable to initialize curl while triggering broadcast. #001';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        $curlSetoptResult = curl_setopt_array($curl, array(
            CURLOPT_POST            => 1,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_POSTFIELDS      => json_encode($request_vars)
        ));
        if ($curlSetoptResult === false) {
            $error_message          = 'Unable to set curl options while triggering broadcast. #002';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        $curlResult = curl_exec($curl);
        if ($curlResult === false) {
            $error_message          = 'Unable to trigger broadcast with internal API. #003 Error message: '.curl_error($curl);
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        curl_close($curl);

        return $response;
    }

}
