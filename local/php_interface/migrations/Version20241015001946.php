<?php

namespace Sprint\Migration;


class Version20241015001946 extends Version
{
    protected $author = "admin";

    protected $description = "Выключение капчи при регистрации";

    protected $moduleVersion = "4.12.6";

    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'admin_lid',
  'VALUE' => 'ru',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'admin_passwordh',
  'VALUE' => 'FVkQeWYUBwUtCUVcAxcFCgsTAQ==',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'allow_qrcode_auth',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'allow_socserv_authorization',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'ALLOW_SPREAD_COOKIE',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'all_bcc',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'attach_images',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'auth_components_template',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'auth_controller_sso',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'auth_multisite',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'auto_time_zone',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'bx_fast_download',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_arBGColor_1',
  'VALUE' => 'FFFFFF',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_arBGColor_2',
  'VALUE' => 'FFFFFF',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_arBorderColor',
  'VALUE' => '000000',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_arEllipseColor_1',
  'VALUE' => '7F7F7F',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_arEllipseColor_2',
  'VALUE' => 'FFFFFF',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_arLineColor_1',
  'VALUE' => 'FFFFFF',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_arLineColor_2',
  'VALUE' => 'FFFFFF',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_arTextColor_1',
  'VALUE' => '000000',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_arTextColor_2',
  'VALUE' => '000000',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_arTTFFiles',
  'VALUE' => 'bitrix_captcha.ttf',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_bLinesOverText',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_bWaveTransformation',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_letters',
  'VALUE' => 'ABCDEFGHJKLMNPQRSTWXYZ23456789',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_numEllipses',
  'VALUE' => '0',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_numLines',
  'VALUE' => '0',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_presets',
  'VALUE' => '2',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'captcha_registration',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'captcha_restoring_password',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_textAngel_1',
  'VALUE' => '-15',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_textAngel_2',
  'VALUE' => '15',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_textDistance_1',
  'VALUE' => '-2',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_textDistance_2',
  'VALUE' => '-2',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_textFontSize',
  'VALUE' => '26',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_textStartX',
  'VALUE' => '40',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CAPTCHA_transparentTextPercent',
  'VALUE' => '0',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'collect_geonames',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'compres_css_js_files',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'control_file_duplicates',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'convert_mail_header',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'convert_original_file_name',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'CONVERT_UNIX_NEWLINE_2_WINDOWS',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'cookie_name',
  'VALUE' => 'BITRIX_SM',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'custom_register_page',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'device_history_cleanup_days',
  'VALUE' => '180',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'disk_space',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'duplicates_max_size',
  'VALUE' => '100',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'email_from',
  'VALUE' => 'sale@',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'error_reporting',
  'VALUE' => '85',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_block_user',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_cleanup_days',
  'VALUE' => '7',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_file_access',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_group_policy',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_login_fail',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_login_success',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_logout',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_marketplace',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_module_access',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_password_change',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_password_request',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_permissions_fail',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_register',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_register_fail',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_task',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_user_delete',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_user_edit',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'event_log_user_groups',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'fill_to_mail',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'GROUP_DEFAULT_RIGHT',
  'VALUE' => 'D',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'GROUP_DEFAULT_TASK',
  'VALUE' => '1',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'hide_panel_for_users',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'imageeditor_proxy_enabled',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'imageeditor_proxy_white_list',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'image_resize_quality',
  'VALUE' => '95',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'inactive_users_block_days',
  'VALUE' => '0',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'INSTALL_STATISTIC_TABLES',
  'VALUE' => '13.10.2024 11:25:04',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'mail_additional_parameters',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'mail_event_bulk',
  'VALUE' => '5',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'mail_event_period',
  'VALUE' => '14',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'mail_gen_text_version',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'mail_link_protocol',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'map_left_menu_type',
  'VALUE' => 'left',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'map_top_menu_type',
  'VALUE' => 'top',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'max_file_size',
  'VALUE' => '20000000',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'move_js_to_body',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'mp_modules_date',
  'VALUE' => 
  array (
    0 => 
    array (
      'ID' => 'sprint.migration',
      'NAME' => 'Миграции для разработчиков',
      'TMS' => 1728931388,
    ),
  ),
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'new_user_email_auth',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'new_user_email_required',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'new_user_email_uniq_check',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'new_user_phone_auth',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'new_user_phone_required',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'new_user_registration',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'new_user_registration_cleanup_days',
  'VALUE' => '7',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'new_user_registration_def_group',
  'VALUE' => '6',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'new_user_registration_email_confirmation',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'optimize_css_files',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'optimize_js_files',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'PARAM_MAX_SITES',
  'VALUE' => '2',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'PARAM_MAX_USERS',
  'VALUE' => '0',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'phone_number_default_country',
  'VALUE' => '1',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'profile_history_cleanup_days',
  'VALUE' => '0',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'profile_image_height',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'profile_image_size',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'profile_image_width',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_assign_authority_group',
  'VALUE' => '4',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_assign_authority_group_add',
  'VALUE' => '2',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_assign_authority_group_delete',
  'VALUE' => '2',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_assign_rating_group',
  'VALUE' => '3',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_assign_rating_group_add',
  'VALUE' => '1',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_assign_rating_group_delete',
  'VALUE' => '1',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_assign_type',
  'VALUE' => 'auto',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_authority_rating',
  'VALUE' => '2',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_authority_weight_formula',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_community_authority',
  'VALUE' => '30',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_community_last_visit',
  'VALUE' => '90',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_community_size',
  'VALUE' => '1',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_count_vote',
  'VALUE' => '10',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_normalization',
  'VALUE' => '10',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_normalization_type',
  'VALUE' => 'auto',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_self_vote',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_start_authority',
  'VALUE' => '3',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_text_like_d',
  'VALUE' => 'Это нравится',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_text_like_n',
  'VALUE' => 'Не нравится',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_text_like_y',
  'VALUE' => 'Нравится',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_vote_show',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_vote_template',
  'VALUE' => 'like',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_vote_type',
  'VALUE' => 'like',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'rating_vote_weight',
  'VALUE' => '10',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'save_original_file_name',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'secure_logout',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'send_mid',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'server_name',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'server_uniq_id',
  'VALUE' => 'cl9sjljqgmybk67sbvw8qam6qa381oqc',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'session_auth_only',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'session_expand',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'session_show_message',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'show_panel_for_users',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'signer_default_key',
  'VALUE' => '383001319fc468f27e0314c964f5a0042f27d55aa15460436f47299a86823d943ebe790c1cc761768283a8a1052881556b11b19b5a5cf03a8e2f6865f01d9b28',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'site_name',
  'VALUE' => 'Современная Одежда+',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'smile_gallery_id',
  'VALUE' => '1',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'smile_last_update',
  'VALUE' => '1728818675',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'sms_default_service',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'stable_versions_only',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'store_password',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'strong_update_check',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'track_outgoing_emails_click',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'track_outgoing_emails_read',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'translate_key_yandex',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'translit_original_file_name',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_autocheck',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_devsrv',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_is_gzip_installed',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_load_timeout',
  'VALUE' => '30',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_safe_mode',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_site',
  'VALUE' => 'www.1c-bitrix.ru',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_site_ns',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_site_proxy_addr',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_site_proxy_pass',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_site_proxy_port',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_site_proxy_user',
  'VALUE' => '',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_stop_autocheck',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_system_check',
  'VALUE' => '14.10.2024 18:43:01',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'update_use_https',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'upload_dir',
  'VALUE' => 'upload',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'url_preview_enable',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'url_preview_save_images',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'user_device_geodata',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'user_device_history',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'user_device_notify',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'user_profile_history',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'use_digest_auth',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'use_encrypted_auth',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'use_hot_keys',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'use_minified_assets',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'use_secure_password_cookies',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'use_time_zones',
  'VALUE' => 'N',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'vendor',
  'VALUE' => '1c_bitrix',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => 'wizard_firsteshop_s1',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => '~new_license18_0_sign',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => '~sale_converted_15',
  'VALUE' => 'Y',
));
        $helper->Option()->saveOption(array (
  'MODULE_ID' => 'main',
  'NAME' => '~sale_paysystem_converted',
  'VALUE' => 'Y',
));
    }
}
