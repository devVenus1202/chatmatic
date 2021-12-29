<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * App\Integration
 *
 * @property int $uid
 * @property int $integration_type_uid
 * @property int $page_uid
 * @property string $parameters
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\IntegrationType $integrationType
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration whereIntegrationTypeUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @property \Illuminate\Support\Carbon $updated_at_utc
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\IntegrationRecord[] $integrationRecords
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Integration whereUpdatedAtUtc($value)
 * @property-read int|null $integration_records_count
 */
class Integration extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'integrations';
    protected $primaryKey   = 'uid';
    protected $guarded      = ['uid'];

    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function integrationType()
    {
        return $this->belongsTo(IntegrationType::class, 'integration_type_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function integrationRecords()
    {
        return $this->hasMany(IntegrationRecord::class, 'integration_uid');
    }

    /**
     * @param $subscriber_uid
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send($subscriber_psid)
    {
        $response = [
            'success'               => 0,
            'error'                 => 0,
            'error_msg'             => '',
            'integration_record'    => []
        ];

        /** @var \App\Subscriber $subscriber */
        $subscriber = $this->page->subscribers()->where('user_psid', $subscriber_psid)->first();

        if( ! $subscriber)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Subscriber not found';

            return $response;
        }

        /** @var \App\IntegrationType $integration_type */
        $integration_type = $this->integrationType;

        switch($integration_type->slug)
        {
            case 'webhook':
                $subscriber_tags = $subscriber->tags()->get();

                $payload = [
                    'psid'              => $subscriber->user_psid,
                    'email'             => $subscriber->email ?? '',
                    'phone_number'      => $subscriber->phone_number ?? '',
                    'first_name'        => $subscriber->first_name ?? '',
                    'last_name'         => $subscriber->last_name ?? '',
                    'locale'            => $subscriber->locale ?? '',
                    'timezone'          => $subscriber->timezone ?? '',
                    'gender'            => $subscriber->gender ?? '',
                    'subscribed_at'     => $subscriber->created_at_utc->toDateTimeString(),
                    'lat'               => $subscriber->lat ?? '',
                    'long'              => $subscriber->lon ?? '',
                    'profile_photo_url' => $subscriber->profile_pic_url ?? '',
                    'tag1'              => isset($subscriber_tags[0]) ? $subscriber_tags[0]->value : '',
                    'tag2'              => isset($subscriber_tags[1]) ? $subscriber_tags[1]->value : '',
                    'tag3'              => isset($subscriber_tags[2]) ? $subscriber_tags[2]->value : '',
                    'tag4'              => isset($subscriber_tags[3]) ? $subscriber_tags[3]->value : '',
                    'tag5'              => isset($subscriber_tags[4]) ? $subscriber_tags[4]->value : '',
                    'tag6'              => isset($subscriber_tags[5]) ? $subscriber_tags[5]->value : '',
                    'tag7'              => isset($subscriber_tags[6]) ? $subscriber_tags[6]->value : '',
                    'tag8'              => isset($subscriber_tags[7]) ? $subscriber_tags[7]->value : '',
                    'tag9'              => isset($subscriber_tags[8]) ? $subscriber_tags[8]->value : '',
                    'tag10'             => isset($subscriber_tags[9]) ? $subscriber_tags[9]->value : '',
                ];

                // Include all custom fields, and their responses if there is one
                $custom_fields = $subscriber->page->customFields()->get();
                foreach($custom_fields as $custom_field)
                {
                    // Empty/placeholder for this custom field with no value
                    $payload[$custom_field->field_name] = '';

                    // If there's a response we'll overwrite with that value
                    if($custom_field_response = $subscriber->customFieldResponses()->where('custom_field_uid', $custom_field->uid)->first())
                    {
                        $payload[$custom_field->field_name] = $custom_field_response->response;
                    }
                }

                $parameters = json_decode($this->parameters);
                $url        = $parameters->webhook_url;

                /** @var \App\IntegrationRecord $integration_record */
                $integration_record     = $this->integrationRecords()->create([
                    'integration_type_uid'  => $this->integration_type_uid,
                    'page_uid'              => $this->page_uid,
                    'success'               => false,
                    'payload'               => json_encode($payload),
                ]);

                $client             = new Client();
                $webhook_response   = $client->request('POST', $url, ['form_params' => $payload]);

                $webhook_response   = json_decode($webhook_response->getBody());

                // Log the result to our audit log for now to see what we get back from various webhook destinations
                $message = [
                    'response'  => $webhook_response,
                    'source'    => $url
                ];
                $audit_log = [
                    'chatmatic_user_uid'    => $subscriber->page->created_by,
                    'page_uid'              => $subscriber->page->uid,
                    'event'                 => 'integration.trigger.result',
                    'message'               => json_encode($message, JSON_UNESCAPED_SLASHES)
                ];
                AuditLog::create($audit_log);

                /* We can't do this because we have no idea what some of these are going to respond with
                if($webhook_response->status !== "success")
                {
                    $response['error']      = 1;
                    $response['error_msg']  = 'Webhook failed to send';

                    $integration_record->success    = false;
                    $integration_record->response   = json_encode($webhook_response);
                    $integration_record->save();

                    return $response;
                }
                */

                $integration_record->success    = true;
                $integration_record->response   = json_encode($webhook_response);
                $integration_record->save();

                break;
        }

        $response['success']            = 1;
        $response['integration_record'] = $integration_record;

        return $response;
    }
}
