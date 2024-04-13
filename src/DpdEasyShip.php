<?php

namespace craftsnippets\dpdeasyship;

use Craft;
use craft\base\Element;
use craft\base\Model;
use craft\base\Plugin;
use craft\commerce\elements\Order;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\services\Utilities;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use craftsnippets\dpdeasyship\behaviors\DpdEasyShipOrderBahavior;
use craftsnippets\dpdeasyship\elements\actions\CreateParcelsAction;
use craftsnippets\dpdeasyship\elements\actions\PrintLabelsAction;
use craftsnippets\dpdeasyship\elements\actions\UpdateParcelsStatusAction;
use craftsnippets\dpdeasyship\fields\DpdEasyShipField as DpdEasyShipAlias;
use craftsnippets\dpdeasyship\models\Settings;
use craftsnippets\dpdeasyship\services\DpdEasyShipService;
use craftsnippets\dpdeasyship\utilities\DpdEasyshipUtility;
use craftsnippets\dpdeasyship\variables\DpdEasyShipVariable;
use yii\base\Event;

/**
 * DPD EasyShip plugin
 *
 * @method static DpdEasyShip getInstance()
 * @method Settings getSettings()
 * @property-read DpdEasyShipService $dpdEasyShipService
 */
class DpdEasyShip extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => ['easyShip' => DpdEasyShipService::class],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });

        $this->easyShip->insertShippingInterface();
        $this->easyShip->addValidationRules();

    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('dpd-easy-ship/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
            'codTypeOptions' => $this->easyShip->getCodTypeOptions(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = DpdEasyShipAlias::class;
        });

        Event::on(
            Order::class,
            Element::EVENT_REGISTER_ACTIONS,
            function(RegisterElementActionsEvent $event) {
                $event->actions[] = PrintLabelsAction::class;
                $event->actions[] = CreateParcelsAction::class;
                $event->actions[] = UpdateParcelsStatusAction::class;
            }
        );

        Event::on(
            Order::class,
            Order::EVENT_DEFINE_BEHAVIORS,
            function(DefineBehaviorsEvent $e) {
                $e->behaviors['dpdEasyshipBehavior'] = DpdEasyShipOrderBahavior::class;
            }
        );

        Event::on(
            Order::class,
            Element::EVENT_REGISTER_HTML_ATTRIBUTES,
            function(\craft\events\RegisterElementHtmlAttributesEvent $event) {
                $order = $event->sender;

                // for actions
                if($order->dpdEasyship->createParcelsActionAllowed()){
                    $event->htmlAttributes['data-dpd-easyship-create-allowed'] = true;
                }
                if($order->dpdEasyship->updateParcelsActionAllowed()){
                    $event->htmlAttributes['data-dpd-easyship-update-allowed'] = true;
                }
                if($order->dpdEasyship->getLabelActionAllowed()){
                    $event->htmlAttributes['data-dpd-easyship-label-allowed'] = true;
                }
            }
        );

        Event::on(
            Order::class,
            Order::EVENT_REGISTER_TABLE_ATTRIBUTES,
            function(RegisterElementTableAttributesEvent $e) {
                $e->tableAttributes['dpdEasyShipStatus'] = [
                    'label' => Craft::t('dpd-easy-ship', 'DPD EasyShip parcels status'),
                ];
            });

        Event::on(
            Order::class,
            Order::EVENT_DEFINE_ATTRIBUTE_HTML,
            function(\craft\events\DefineAttributeHtmlEvent $e) {
                if($e->attribute === 'dpdEasyShipStatus'){
                    $order = $e->sender;
                    $e->html = $order->dpdEasyship->getIndexColumnStatusesSummary();
                }
            }
        );


        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('dpdEasyship', DpdEasyShipVariable::class);
            }
        );

        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => 'DPD EasyShip',
                    'permissions' => [
                        'manageDpdEasyship' => [
                            'label' => Craft::t('dpd-easy-ship', 'Manage DDP EasyShip parcels'),
                        ],
                    ],
                ];
            }
        );
        Event::on(Utilities::class, Utilities::EVENT_REGISTER_UTILITIES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = DpdEasyshipUtility::class;
        });

    }
}
