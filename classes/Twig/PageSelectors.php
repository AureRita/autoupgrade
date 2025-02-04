<?php

namespace PrestaShop\Module\AutoUpgrade\Twig;

class PageSelectors
{
    public const APP_PARENT_ID = 'update_assistant';
    public const PAGE_PARENT_ID = 'ua_page';
    public const STEP_PARENT_ID = 'ua_container';
    public const STEPPER_PARENT_ID = 'stepper_content';
    public const DIALOG_PARENT_ID = 'ua_dialog';
    public const RADIO_CARD_ONLINE_PARENT_ID = 'radio_card_online';
    public const RADIO_CARD_ARCHIVE_PARENT_ID = 'radio_card_archive';
    public const DOWNLOAD_LOGS_PARENT_ID = 'download_logs';

    /**
     * @return array<string, string>
     */
    public static function getAllSelectors(): array
    {
        return [
            'app_parent_id' => self::APP_PARENT_ID,
            'page_parent_id' => self::PAGE_PARENT_ID,
            'step_parent_id' => self::STEP_PARENT_ID,
            'stepper_parent_id' => self::STEPPER_PARENT_ID,
            'dialog_parent_id' => self::DIALOG_PARENT_ID,
            'radio_card_online_parent_id' => self::RADIO_CARD_ONLINE_PARENT_ID,
            'radio_card_archive_parent_id' => self::RADIO_CARD_ARCHIVE_PARENT_ID,
            'download_logs_parent_id' => self::DOWNLOAD_LOGS_PARENT_ID,
        ];
    }
}
