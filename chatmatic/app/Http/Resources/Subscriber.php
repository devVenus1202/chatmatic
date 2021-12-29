<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Subscriber extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Build location string
        $location = null;
        if($this->lat !== null && $this->lon !== null)
        {
            $location = $this->lat.','.$this->lon;
        }

        // Populate return array
        $return = [
            'uid'                               => $this->uid,
            'psid'                              => $this->user_psid,
            'page_uid'                          => $this->page_uid,
            'email'                             => $this->email,
            'phone'                             => $this->phone_number,
            'location'                          => $location,
            'first_name'                        => $this->first_name,
            'last_name'                         => $this->last_name,
            'gender'                            => $this->gender,
            'locale'                            => $this->locale,
            'timezone'                          => $this->timezone,
            'profile_pic_url'                   => $this->profile_pic_url, // TODO: This appears to be expiring, but this request could have thousands of subscribers as such we don't want to be re-requesting a new profile photo url for all of them... might need to make this request on the front end.
            'messages_read'                     => $this->messages_read,
            'messages_sent'                     => $this->messages_accepted_from_page,
            'total_clicks'                      => $this->total_clicks,
            'pause_subscriptions_until_utc'     => $this->pause_subscriptions_until_utc,
            'last_engagement_utc'               => (string) $this->last_engagement_utc,
            'last_subscriber_action_utc'        => $this->last_subscriber_action_utc,
            'created_at_utc'                    => (string) $this->created_at_utc,
            'updated_at_utc'                    => (string) $this->updated_at_utc,
            'is_subscribed'                     => $this->active,
        ];

        return $return;
    }
}
