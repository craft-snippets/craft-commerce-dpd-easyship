<?php

namespace craftsnippets\dpdeasyship\elements\actions;

use Craft;
use craft\base\ElementAction;

/**
 * Print Labels Action element action
 */
class PrintLabelsAction extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('dpd-easy-ship', 'DPD EasyShip - get parcel labels');
    }

    public function getTriggerHtml(): ?string
    {
        Craft::$app->getView()->registerJsWithVars(fn($type) => <<<JS
            (() => {
                new Craft.ElementActionTrigger({
                    type: $type,

                    // Whether this action should be available when multiple elements are selected
                    bulk: true,

                    // Return whether the action should be available depending on which elements are selected
                    validateSelection: function(selectedItems) {
                        var allowed = true;
                        // selectedItems is object instead of regular array
                        for (let key in selectedItems) {
                                if (!isNaN(parseInt(key))) {
                                    let single = selectedItems[key];
                                    if(single.querySelector('[data-dpd-easyship-update-allowed]') == null){
                                        allowed = false;
                                    }    
                                }
                        }                  
                        return allowed;
                    },

                    // Uncomment if the action should be handled by JavaScript:
                    activate: function() {
                      Craft.elementIndex.setIndexBusy();
                      const ids = Craft.elementIndex.getSelectedElementIds();
                      // ...
                      console.log(ids);
                      let url = Craft.getActionUrl('dpd-easy-ship/api/print-labels', {orderIds: ids});
                      window.open(url, "_blank");
                      Craft.elementIndex.setIndexAvailable();
                    },
                });
            })();
        JS, [static::class]);
        return null;
    }

}
