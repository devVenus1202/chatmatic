<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

/**
 * App\TriggerConfBroadcast
 *
 * @property int $uid
 * @property string|null $broadcast_type
 * @property string|null $intention
 * @property string|null $start_time_utc
 * @property \Carbon\Carbon|null $end_time_utc
 * @property string|null $conditions_json
 * @property int $status
 * @property string|null $facebook_messaging_type
 * @property string|null $facebook_messaging_tag
 * @property-read \App\WorkflowTrigger $workflowTrigger
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereBroadcastType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereConditionsJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereEndTimeUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereFacebookMessagingTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereFacebookMessagingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereIntention($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereStartTimeUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereWorkflowUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast query()
 * @property string|null $fire_at_utc
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TriggerConfBroadcast whereFireAtUtc($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Interaction[] $interactions
 * @property-read int|null $interactions_count
 */
class TriggerConfBroadcast extends Model
{
    public $timestamps      = false;
    
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'trigger_conf_broadcasts';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

     protected $dates = [
        'end_time_utc'
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
    public function workflowTrigger()
    {
        return $this->belongsTo(WorkflowTrigger::class, 'workflow_trigger_uid', 'uid');
    }

    /**
     * A relation to pick up the messages that were a result of this broadcast - which are recorded with their
     * workflow_uid on the interactions table - so take note that the workflow_uid is the local key on the relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'workflow_trigger_uid', 'workflow_trigger_uid');
    }

    /**
     * @return int|string
     */
    public function statusString()
    {
        $status = $this->status;
        switch($this->status)
        {
            case 0:
                $status = 'Not-Scheduled';
                break;

            case 1:
                $status = 'Scheduled';
                break;

            case 2:
                $status = 'In-progress';
                break;

            case 3:
                $status = 'Complete';
                break;
        }

        return $status;
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

        // Extract the needed options to the broadcast
        $broadcast_type              = $request_options['broadcast_type'];
        $intention                   = $request_options['intention'];
        $conditions_json             = $request_options['conditions_json'];
        $facebook_messaging_type     = $request_options['facebook_messaging_type'];
        $facebook_messaging_tag      = $request_options['facebook_messaging_tag'];
        $fire_at_utc                 = $request_options['fire_at_utc'];
        $optimized                   = $request_options['optimized'];


        // Broadcast type

        if ( ! isset($broadcast_type) )
        {
            // The request has not any keyword
            $response['error'] = 1;
            $response['error_msg'] = 'A broadcast type is needed.';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        $allowed_types = ['non-promotional', 'marketing'];
        if( ! in_array($broadcast_type, $allowed_types, true))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'The type _'.$broadcast_type.'_ is not a valid option.';

            return $response;
        }



        // Intention

        if ( ! isset($intention) )
        {
            // The request has not any keyword
            $response['error'] = 1;
            $response['error_msg'] = 'A intention is needed.';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        // facebook messaging type

        if ( ! isset($facebook_messaging_type) )
        {
            // The request has not any keyword
            $response['error'] = 1;
            $response['error_msg'] = 'A messagign type is needed';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        $allowed_messaging_types = ['MESSAGE_TAG', 'UPDATE'];
        if( ! in_array($facebook_messaging_type, $allowed_messaging_types, true))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'The messaging type _'.$facebook_messaging_type.'_ is not a valid option.';

            return $response;
        }

        // facebook messaging tag

        if ( (! isset($facebook_messaging_tag ) ) && $facebook_messaging_type === 'MESSAGE_TAG')
        {
            // The request has not any keyword
            $response['error'] = 1;
            $response['error_msg'] = 'A messagign tag is needed';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        $allowed_messaging_tags = ['CONFIRMED_EVENT_UPDATE', 'POST_PURCHASE_UPDATE', 'ACCOUNT_UPDATE'];
        if( ( ! in_array($facebook_messaging_tag, $allowed_messaging_tags, true) ) && $facebook_messaging_type === 'MESSAGE_TAG' )
        {
            $response['error'] = 1;
            $response['error_msg'] = 'The messaging tag _'.$facebook_messaging_tag.'_ is not a valid option.';

            return $response;
        }

        // Let's validate the correct combination
        // Non promotional
        if ( $broadcast_type === 'non-promotional' && $facebook_messaging_type !== 'MESSAGE_TAG' )
        {
            $response['error']          = 1;
            $response['error_msg']      = 'Facebook message type should be MESSAGE_TAG  when setting up a non-promotional broadcast';

            return $response;
        }

        if ( $broadcast_type === 'marketing' && $facebook_messaging_type !== 'UPDATE')
        {
            $response['error']          = 1;
            $response['error_msg']      = 'Facebook message type should be UPDATE when setting up a marketing broadcast';

            return $response;
        }


        if ( $facebook_messaging_type === 'UPDATE' )
        {
            $facebook_messaging_tag = null;
        }


        // Once created the workflow trigger, let's create the workflow trigger
        $broadcast                                     = new self;
        $broadcast->broadcast_type                     = $broadcast_type;
        $broadcast->intention                          = $intention;
        $broadcast->conditions_json                    = json_encode($conditions_json);
        $broadcast->status                             = 1;
        $broadcast->facebook_messaging_type            = $facebook_messaging_type;
        $broadcast->facebook_messaging_tag             = $facebook_messaging_tag;
        $broadcast->fire_at_utc                        = $fire_at_utc;
        $broadcast->optimized                          = $optimized;
        $broadcast->workflow_trigger_uid               = $workflow_trigger->uid;


        $broadcast_saved = $broadcast->save();
        if( ! $broadcast_saved)
        {
            // Saving the keyword config trigger failed
            $response['error'] = 1;
            $response['error_msg'] = 'Error saving m dot me config trigger';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }


        return $response;
    }
}
