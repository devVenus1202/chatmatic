<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowStep
 *
 * @property int $uid
 * @property int $workflow_uid
 * @property string $step_type
 * @property string $step_type_parameters
 * @property string $name
 * @property int $page_uid
 * @property-read \App\Page $page
 * @property-read \App\Workflow $workflow
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowStepItem[] $workflowStepItems
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep whereStepType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep whereStepTypeParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep whereWorkflowUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep query()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\QuickReply[] $quickReplies
 * @property bool $favorite
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep whereFavorite($value)
 * @property int|null $custom_field_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStep whereCustomFieldUid($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowStepOptionCondition[] $optionConditions
 * @property-read int|null $option_conditions_count
 * @property-read \App\WorkflowStepOptionDelay|null $optionDelay
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowStepOptionRandom[] $optionRandomizations
 * @property-read int|null $option_randomizations_count
 * @property-read \App\WorkflowStepSms|null $optionSms
 * @property-read int|null $quick_replies_count
 * @property-read int|null $workflow_step_items_count
 */
class WorkflowStep extends Model
{
    protected $table        = 'workflow_steps';
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowStepItems()
    {
        return $this->hasMany(WorkflowStepItem::class, 'workflow_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quickReplies()
    {
        return $this->hasMany(QuickReply::class, 'workflow_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function optionDelay()
    {
        return $this->hasOne(WorkflowStepOptionDelay::class, 'workflow_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function optionRandomizations()
    {
        return $this->hasMany(WorkflowStepOptionRandom::class, 'workflow_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function optionConditions()
    {
        return $this->hasMany(WorkflowStepOptionCondition::class, 'workflow_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function optionSms()
    {
        return $this->hasOne(WorkflowStepSms::class, 'workflow_step_uid', 'uid');
    }

    /**
     * Prepare this WorkflowStep record object to be templated by removing unneeded values and setting
     * appropriate template values
     *
     * @param WorkflowStep $workflowStep
     * @param WorkflowTemplate $workflowTemplate
     * @return array
     */
    public static function prepareForTemplate(WorkflowStep $workflowStep, WorkflowTemplate $workflowTemplate)
    {
        $workflowTemplateStep = $workflowStep->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowTemplateStep['uid'],
            $workflowTemplateStep['workflow_uid'],
            $workflowTemplateStep['page_uid'],
            $workflowTemplateStep['favorite'],
            $workflowTemplateStep['custom_field_uid'],
            $workflowTemplateStep['child_step_uid'],
            $workflowTemplateStep['messages_delivered'],
            $workflowTemplateStep['messages_read'],
            $workflowTemplateStep['messages_clicked'],
            $workflowTemplateStep['messages_reached']
        );

        // Set the stuff we need
        $workflowTemplateStep['workflow_template_uid'] = $workflowTemplate->uid;
        $workflowTemplateStep['child_step_uid'] = $workflowTemplate->child_step_uid;

        // Return the array
        return $workflowTemplateStep;
    }

    public function retrieveJson($flowTriggerUid)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
        ];

        // Make request to Pipeline for count of subscribers
        $pipeline_internal_base_url = \Config::get('chatmatic.pipeline_internal_base_url');

        // Setup request to pipeline to get the scan code
        $post_array                 = [
            'workflow_step_uid'    => $this->uid,
            'trigger_uid'          => $flowTriggerUid
        ];

        // Call the python pipelin
        $curl = curl_init($pipeline_internal_base_url . '/workflow-json');
        if ($curl === false) {
            $error_message          = 'Unable to obtain json step because of internal error. #001';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        $curlSetoptResult = curl_setopt_array($curl, array(
            CURLOPT_POST            => 1,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_POSTFIELDS      => json_encode($post_array)
        ));
        if ($curlSetoptResult === false) {
            $error_message          = 'Unable to obtain json step because of internal error. #002';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        $curl_result = curl_exec($curl);
        if ($curl_result === false) {
            $error_message          = 'Unable to obtain  json step because of internal error. #003';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        curl_close($curl);

        $curl_result_json = json_decode($curl_result);

        if (json_last_error() === JSON_ERROR_NONE) {
            $response['json_step'] = $curl_result_json->json;
        }

        return $response;

    }
}
