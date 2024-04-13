<?php

namespace craftsnippets\dpdeasyship\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\base\SortableFieldInterface;
use craft\elements\db\ElementQueryInterface;
use craft\fields\conditions\TextFieldConditionRule;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craftsnippets\dpdeasyship\DpdEasyShip;
use yii\db\Schema;

/**
 * Dpd Easy Ship field type
 */
class DpdEasyShipField extends Field
{
    public static function displayName(): string
    {
        return Craft::t('dpd-easy-ship', 'Dpd EasyShip');
    }

    public static function valueType(): string
    {
        return 'mixed';
    }

    public function getSettingsHtml(): ?string
    {
        return null;
    }

    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    public function normalizeValue(mixed $value, ElementInterface $element = null): mixed
    {
        return $value;
    }

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        if(DpdEasyShip::getInstance()->getSettings()->hideField == true){
            $id = Html::id($this->handle);
            $namespacedId = Craft::$app->getView()->namespaceInputId($id);
            $css = <<<CSS
            #{$namespacedId}-field {
                display: none;
            }
            CSS;
            Craft::$app->getView()->registerCss($css);
        }
        $options = [
            'style' => [
                'width' => '100%',
                'height' => '150px',
            ],
        ];
        return Html::textarea($this->handle, $value, $options);
    }

    protected function searchKeywords(mixed $value, ElementInterface $element): string
    {
        $parcels = $element->dpdEasyShip->parcels;
        $numbers = array_column($parcels, 'number');
        return StringHelper::toString($numbers, ' ');
    }

    public static function isRequirable(): bool
    {
        return false;
    }
    public function getIsTranslatable(?ElementInterface $element = null): bool
    {
        return false;
    }

    public static function supportedTranslationMethods(): array
    {
        return [
            self::TRANSLATION_METHOD_NONE,
        ];
    }

}
