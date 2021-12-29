<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TriggerConfButton
 *
 * @property int $uid
 * @property string $words
 * @property string $options
 * @property-read \App\WorkflowTrigger $workflowTrigger
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfKeyword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfKeyword newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfKeyword query()
 * @mixin \Eloquent
 */

class TriggerConfKeyword extends Model
{

    public $timestamps      = false;

    protected $table        = 'trigger_conf_keywords';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];


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

        // Extract the needed options for keywords
        $keywords               = $request_options['keywords'];
        $keyword_option         = $request_options['keywords_option'];
        $config_uid             = $request_options['uid'] ?? null ;

        // Let's validate we have keywords
        if ( ! isset($keywords) )
        {
            // The request has not any keyword
            $response['error'] = 1;
            $response['error_msg'] = 'Keywords are needed to save this message.';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        // Let's validate we have a keyword option
        if ( ! isset($keyword_option) )
        {
            // The request has not any keyword
            $response['error'] = 1;
            $response['error_msg'] = 'It is necessary to assing a keyword option.';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        // Validate keyword options
        $valid_options = ['contains_any','exact_match','contains_all'];
        if( ! in_array($keyword_option, $valid_options, true))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'The option _'.$keyword_option.'_ is not valid.';

            return $response;
        }

        // if we have a uid on the options then this is a update otherwise it'a new one

        if ( isset($config_uid) )
        {
            //update
            $keyword               = $workflow_trigger->keyword()->first();

        }
        else
        {
            // New one
            $keyword                = new self;
        }

        // Once created the workflow trigger, let's create the workflow trigger
        
        $keyword->option                       = $keyword_option;
        $keyword->workflow_trigger_uid         = $workflow_trigger->uid;


        // If it's an empty array
        if(is_array($keywords) && count($keywords) < 1)
        {
            $keyword->words     = '';
        }
        // If it's a populated array, implode it into a comma delimited string
        elseif(is_array($keywords) && count($keywords) > 0)
        {
            $keyword->words = implode(',', $keywords);
        }
        // If it's a string and it's not empty
        elseif(is_string($keywords) && $keywords !== '')
        {
            $keyword->words     = $keywords;
        }
        // Some other situation where we'll just blank it
        else
        {
            $keyword->words = '';
        }

        $keyword_saved = $keyword->save();

        if( ! $keyword_saved)
        {
            // Saving the keyword config trigger failed
            $response['error'] = 1;
            $response['error_msg'] = 'Error saving keyword config trigger';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }


        return $response;
    }


}
