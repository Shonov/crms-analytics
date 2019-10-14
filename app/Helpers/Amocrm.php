<?php
/**
 * Created by PhpStorm.
 * User: Виталий Шонов
 * Date: 17.05.2019
 * Time: 13:32
 */

namespace App\Helpers;

use stdClass;

class Amocrm
{
    public $LEAD_LANG_AR = 'Arabic';
    public $LEAD_LANG_EN = 'English';
    public $STATUS_TRIAL_REQUEST;
    public $STATUS_QUESTIONNAIRE;
    public $STATUS_QUESTIONNAIRE_DONE;
    public $STATUS_TRIAL_SCHEDULED;
    public $STATUS_NO_SHOW_CANCELLED;
    public $STATUS_TRIAL_COMPLETED;
    public $STATUS_NO_REPLY_AFTER_TRIAL;
    public $STATUS_THINKING_DECISION;
    public $STATUS_FIRST_MEMBERSHIP_SCHEDULED;
    public $STATUS_NO_SHOW_FIRST_MEMBERSHIP;
    public $STATUS_FIRST_MEMBERSHIP_DONE;
    public $STATUS_PURCHASED;
    public $STATUS_NO_PURCHASE;

    public $CUSTOM_FIELD_LANGUAGE;
    public $CUSTOM_FIELD_EMAIL;

    protected $settings;
    protected $subdomain;
    protected $auth;

    public $STAGE_PURCHASED;
    public $STAGE_4_WEEKS;
    public $STAGE_3_WEEKS;
    public $STAGE_2_WEEKS;
    public $STAGE_1_WEEKS;
    public $STAGE_MEMBERSHIP_FINISHED;
    public $STAGE_INTERESTED_TO_RENEW;
    public $STAGE_NO_MORE_INTEREST;

    public $END_SUBSCRIPTIONS_DAY;

    public $LEAD_LANGUAGE;
    public $CONTACT_LANGUAGE;

    public $CONTACT_LANG_EN;
    public $CONTACT_LANG_AR;

    public function __construct()
    {
        $properties = config('amocrm.' . env('PROJECT_NAME') . '.properties');
        foreach ($this as $key => $value) {
            if (isset($properties[$key])) {
                $this->{$key} = $properties[$key];
            }
        }

        $this->settings = new stdClass();
        $this->settings->amocrm = new stdClass();
        $this->settings->amocrm->api = env('AMO_KEY');
        $this->settings->amocrm->login = env('AMO_LOGIN');
        $this->settings->amocrm->subdomain = env('AMO_SUBDOMAIN');
        $this->amocrm_auth();
    }

    public function amocrm_auth()
    {
        if (isset($this->settings->amocrm)) {
            if ($this->settings->amocrm->api && $this->settings->amocrm->login && $this->settings->amocrm->subdomain) {
                $subdomain = $this->settings->amocrm->subdomain;
                $this->subdomain = $subdomain;
                $user = array(
                    'USER_LOGIN' => $this->settings->amocrm->login,
                    'USER_HASH' => $this->settings->amocrm->api
                );
            } else {
                return 'Нет данных для авторизации';
            }
        } else {
            return 'Нет данных для авторизации';
        }

        $link = 'https://' . $subdomain . '.amocrm.ru/private/api/auth.php?type=json';
        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($user, null, '&', PHP_QUERY_RFC1738));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
        curl_close($curl); #Заверашем сеанс cURL

        $auth = json_decode($out);
        if ($out) $this->auth = $auth->response->auth;

        return $out;
    }

    public function l($l)
    {
        echo '<pre>';
        var_dump($l);
        echo '</pre>';
    }

    public function findLeadBy($query)
    {
        $path = '/private/api/v2/json/leads?' . http_build_query(['query' => $query]);

        return $this->q($path, [], false, 'GET');
    }

    public function findContactBy($query)
    {
        $path = '/private/api/v2/json/contacts?' . http_build_query(['query' => $query]);

        return $this->q($path, [], false, 'GET');
    }
    
    function q($path, $fields = array(), $ifModifiedSince = false)
    {
        return $this->amocrm_query($path, $fields, $ifModifiedSince);
    }

    public function amocrm_query($path, $fields = array(), $ifModifiedSince = false)
    {
        $link = 'https://' . $this->subdomain . '.amocrm.ru' . $path;
        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        if ($ifModifiedSince) {
            $httpHeader = array('IF-MODIFIED-SINCE: ' . $ifModifiedSince);
        } else {
            $httpHeader = array();
        }
        if (count($fields)) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
            $httpHeader[] = 'Content-Type: application/json';
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeader);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        //$this->l(curl_getinfo($curl));
        return json_decode($out);
    }

    public function moveLeadToStatus($userId, $statusId)
    {
        $path = '/api/v2/leads';
        $fields['update'] = [[
            'id' => $userId,
            'updated_at' => time(),
            'status_id' => $statusId,
        ]];

        return $this->q($path, $fields);
    }

    public function createLead($leadName, $statusId, $contactName, $phone, $email, $language = null)
    {
        $formType = [
            'lead_name' => $leadName,
            'status_id' => $statusId,
        ];

        $path = '/private/api/v2/json/leads/set';
        $fields['request']['leads']['add'] = [[
            'name' => $formType['lead_name'],
            'status_id' => $formType['status_id'],
        ]];

        $leadAnswer = $this->q($path, $fields);

        $leadId = $leadAnswer->response->leads->add[0]->id;

        $customFields = [];


        if ($phone !== '' && $phone !== null) {
            $customFields[] = [
                #Телефоны
                'id' => 95927, #Уникальный индентификатор заполняемого дополнительного поля
                'values' => [[
                    'value' => $phone,
                    'enum' => 'MOB' #Мобильный
                ]]
            ];
        }

        if ($email !== '' && $email !== null) {
            $customFields[] = [
                #Emails
                'id' => 95929, #Уникальный индентификатор заполняемого дополнительного поля
                'values' => [[
                    'value' => $email,
                    'enum' => 'WORK' #Мобильный
                ]]
            ];
        }

        if ($language !== null && in_array($language, ['Arabic', 'English'])) {
            $languages = [
                'Arabic' => 164837,
                'English' => 164835
            ];

            $customFields[] = [
                #Emails
                'id' => 123969, #Уникальный индентификатор заполняемого дополнительного поля
                'values' => [[
                    'value' => $languages[$language],
                ]]
            ];
        }

        $contacts['request']['contacts']['add'] = [[
            'name' => $contactName, #Имя контакта
            'linked_leads_id' => [ #Список с айдишниками сделок контакта
                $leadId
            ],
            'custom_fields' => $customFields
        ]];

        $path = '/private/api/v2/json/contacts/set';
        $contactAnswer = $this->q($path, $contacts);
    }

    public function getContactLang($contact)
    {
        foreach ($contact->custom_fields as $customField) {
            if (+$customField->id === $this->CUSTOM_FIELD_LANGUAGE) {
                if ($customField->values[0]->value === $this->LEAD_LANG_AR) {
                    return $this->LEAD_LANG_AR;
                }
            }
        }

        return $this->LEAD_LANG_EN;
    }

    public function getContactEmail($contact)
    {
        foreach ($contact->custom_fields as $customField) {
            if (+$customField->id === $this->CUSTOM_FIELD_EMAIL) {
                return $customField->values[0]->value;
            }
        }

        return null;
    }

    public function getLeadById($id)
    {
        $path = '/private/api/v2/json/leads?' . http_build_query(['id' => $id]);

        return $this->q($path, [], false, 'GET')->response->leads[0];
    }

    public function getContactById($id)
    {
        $path = '/private/api/v2/json/contacts?' . http_build_query(['id' => $id]);

        return $this->q($path, [], false, 'GET')->response->contacts[0];
    }

    public function getCustomFieldValue($id, $customFields)
    {
        foreach ($customFields as $field) {
            if (+$id === +$field->id) {
                return $field->values[0]->value;
            }
        }

        return null;
    }

    public function getCustomers($query)
    {
        $path = '/api/v2/customers?' . http_build_query($query);

        return $this->q($path, [], false, 'GET')->_embedded->items ?? null;
    }

    public function getLeads($query)
    {
        $path = '/api/v2/leads?' . http_build_query($query);

        return $this->q($path, [], false, 'GET')->_embedded->items;
    }


    public function updateCustomers($customers)
    {
        $path = '/private/api/v2/json/customers/set';
        $fields['request']['customers']['update'] = $customers;

        return $this->q($path, $fields);
    }

    public function updateContacts($contacts)
    {
        $path = '/api/v2/contacts';
        $fields['update'] = $contacts;

        return $this->q($path, $fields);
    }

    public function updateLeads($leads)
    {
        $path = '/api/v2/leads';
        $fields['update'] = $leads;

        return $this->q($path, $fields);
    }

    public function isAfterStage($current, $toCompare)
    {
        $firstIndex = 0;
        $secondIndex = 0;

        $stages = [
            $this->STATUS_TRIAL_REQUEST,
            $this->STATUS_QUESTIONNAIRE,
            $this->STATUS_QUESTIONNAIRE_DONE,
            $this->STATUS_TRIAL_SCHEDULED,
            $this->STATUS_NO_SHOW_CANCELLED,
            $this->STATUS_TRIAL_COMPLETED,
            $this->STATUS_NO_REPLY_AFTER_TRIAL,
            $this->STATUS_THINKING_DECISION,
            $this->STATUS_FIRST_MEMBERSHIP_SCHEDULED,
            $this->STATUS_NO_SHOW_FIRST_MEMBERSHIP,
            $this->STATUS_FIRST_MEMBERSHIP_DONE,
            $this->STATUS_PURCHASED,
            $this->STATUS_NO_PURCHASE,
        ];

        foreach ($stages as $key => $stage) {
            if ($stage === $current) {
                $firstIndex = $key;
            }
            if ($stage === $toCompare) {
                $secondIndex = $key;
            }
        }

        return $firstIndex >= $secondIndex;
    }

    public function getTasks($query)
    {
        $path = '/api/v2/tasks?' . http_build_query($query);

        return $this->q($path, [], false, 'GET')->_embedded->items ?? null;
    }
}
