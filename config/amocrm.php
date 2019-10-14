<?php
/**
 * Created by PhpStorm.
 * User: Виталий Шонов
 * Date: 10.07.2019
 * Time: 23:57
 */

/*
|--------------------------------------------------------------------------
| Custom config for Acuity locations
|--------------------------------------------------------------------------
*/

return [
    'bahrain' => [
        'statistic_collection' => [
            'pipeline_id' => 1451539,
            'utm_source_field' => 620991,
        ],
        'properties' => [
            'STATUS_TRIAL_REQUEST' => 22563208,
            'STATUS_QUESTIONNAIRE' => 22563211,
            'STATUS_QUESTIONNAIRE_DONE' => 22563214,
            'STATUS_TRIAL_SCHEDULED' => 22563217,
            'STATUS_NO_SHOW_CANCELLED' => 23077114,
            'STATUS_TRIAL_COMPLETED' => 23077198,
            'STATUS_NO_REPLY_AFTER_TRIAL' => 23077279,
            'STATUS_THINKING_DECISION' => 23591836,
            'STATUS_FIRST_MEMBERSHIP_SCHEDULED' => 23581369,
            'STATUS_NO_SHOW_FIRST_MEMBERSHIP' => 23642251,
            'STATUS_FIRST_MEMBERSHIP_DONE' => 23642344,
            'STATUS_PURCHASED' => 142,
            'STATUS_NO_PURCHASE' => 143,
            'CUSTOM_FIELD_LANGUAGE' => 472259,
            'CUSTOM_FIELD_EMAIL' => 372439,
            'STAGE_PURCHASED' => 1035010,
            'STAGE_4_WEEKS' => 1035016,
            'STAGE_3_WEEKS' => 1035019,
            'STAGE_2_WEEKS' => 1213579,
            'STAGE_1_WEEKS' => 1213582,
            'STAGE_MEMBERSHIP_FINISHED' => 1213585,
            'STAGE_INTERESTED_TO_RENEW' => 1213588,
            'STAGE_NO_MORE_INTEREST' => 1224625,

            'END_SUBSCRIPTIONS_DAY ' => 635095,
            'LEAD_LANGUAGE ' => 472259,
            'CONTACT_LANGUAGE ' => 636485,
            'CONTACT_LANG_EN ' => 1080121,
            'CONTACT_LANG_AR ' => 1080119,
        ],
    ],
    'nexfit' => [
        'statistic_collection' => [
            'utm_source_field' => 133849,
            'pipeline_id' => 1795039,
        ],
        'properties' => [
            'STATUS_TRIAL_REQUEST' => 28009120,
            'STATUS_QUESTIONNAIRE' => 28009219,
            'STATUS_QUESTIONNAIRE_DONE' => 28009225,
            'STATUS_TRIAL_SCHEDULED' => 28009231,
            'STATUS_NO_SHOW_CANCELLED' => 28009237,
            'STATUS_TRIAL_COMPLETED' => 28009240,
            'STATUS_NO_REPLY_AFTER_TRIAL' => 28009243,
            'STATUS_THINKING_DECISION' => 28009246,
            'STATUS_FIRST_MEMBERSHIP_SCHEDULED' => 28009255,
            'STATUS_NO_SHOW_FIRST_MEMBERSHIP' => 28009258,
            'STATUS_FIRST_MEMBERSHIP_DONE' => 28009261,
            'STATUS_PURCHASED' => 142,
            'STATUS_NO_PURCHASE' => 143,
            'CUSTOM_FIELD_LANGUAGE' => 123969,
            'CUSTOM_FIELD_EMAIL' => 95929,
            'STAGE_PURCHASED' => 1035010,
            'STAGE_4_WEEKS' => 2111944,
            'STAGE_3_WEEKS' => 2111947,
            'STAGE_2_WEEKS' => 2111950,
            'STAGE_1_WEEKS' => 2111953,
            'STAGE_MEMBERSHIP_FINISHED' => 2111956,
            'STAGE_INTERESTED_TO_RENEW' => 2111962,
            'STAGE_NO_MORE_INTEREST' => 2111965,

            'END_SUBSCRIPTIONS_DAY ' => 133915,
            'LEAD_LANGUAGE ' => 149153,
            'CONTACT_LANGUAGE ' => 123969,
            'CONTACT_LANG_EN ' => 164835,
            'CONTACT_LANG_AR ' => 164837,
        ],
    ],
];
