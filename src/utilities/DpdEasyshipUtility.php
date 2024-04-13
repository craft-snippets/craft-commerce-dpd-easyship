<?php

namespace craftsnippets\dpdeasyship\utilities;

use Craft;
use craft\base\Utility;

/**
 * Dpd Easyship Utility utility
 */
class DpdEasyshipUtility extends Utility
{
    public static function displayName(): string
    {
        return Craft::t('dpd-easy-ship', 'DPD Easyship');
    }

    static function id(): string
    {
        return 'dpd-easyship-utility';
    }

    public static function iconPath(): ?string
    {
        return null;
    }

    static function contentHtml(): string
    {
        // todo: replace with custom content HTML
        $txt = Craft::t('dpd-easy-ship', 'Update parcels statuses');
        $url = \craft\helpers\UrlHelper::actionUrl('dpd-easy-ship/api/push-parcels-statuses-update-job');
        $html = '<a href="'.$url.'" type="submit" class="btn submit">'.$txt.'</a>';
        return $html;
    }
}
