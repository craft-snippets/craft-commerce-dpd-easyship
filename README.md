## Installation

Run in the console:

```
composer require craftsnippets/craft-dpd-easyship
```

## Allowed countries

Easyship is available for Croatia and Slovenia. This is important for selection of API service of the client, which can be set in the plugin setting "API Country".

Besides that, only sender and recipient addresses of parcels from Croatia and Slovenia are allowed.

## Permissions

In order to be able to use Easyship, control panel user must have "Manage DDP EasyShip parcels" permission enabled.

## DPD EasyShip interface
Dpd Easyship can be only used in Croatia dn Slovenia.

To use DPD Easyship, add ONE field of "Dpd EasyShip" type to order field layout. This field is used only as container for data and will not display any kind of input on order field layout.

Then you need to enable Easyship for specific shipping methods in plugin settings in "Shipping methods with Dpd EasyShip integration enabled" setting. This will make Easyship interface appear in order page and element index actions available fpr specific orders on orders list/

If order has parcels, but then admin removes its shipping method from plugin setting, interface will still show up to make it possible to remove parcels.

## Orders list

Functionalities Added to the order list in the control panel: 

- searching by parcel number.
- additional element index column with parcel statuses.
- element index actions available after selecting one or more orders - create parcels, update parcels, get pdf of parcel labels

## Removing parcels

For some reason API call for removing parcels takes long time to get response. That's why queue job is used there - parcel data is removed from order immediately, but job with api call is pushed to queue manager.

Note that removing parcels with "CREATED" status will remove them from API completely. While removing parcels with "PRINTED" status will just set their status in API as "CANCELLED" (parcel data is still removed from craft commerce in this situation). Parcels that have status "SENT" are not allowed to be removed from API at all and this option is blocked by Easyshp plugin interface.

## Phone number field and delivery instruction field

Addresses in Craft do not have phone number field built-in. Thats why we need to create plain text field, assign it to address field layout and select it in "Phone number field" plugin setting.

Then you need to add this field to the frontend address form. To automatically get this field object you can use this `getAddressPhoneField` function.

```
{% set phoneField = craft.dpdEasyship.getAddressPhoneField() %}
{% if phoneField %}
<input name="fields[{{phoneField.handle}}]">
{% endif %}
```

Delivery instructions field works in similar way, but is added to order field layout, not an address field layout. After adding it there, set it in "Delivery instructions for courier field" plugin setting and add it to frontend cart/order form:

```
{% set instructionsField = craft.dpdEasyship.getOrderInstructionsField() %}
{% if instructionsField %}
<textarea name="fields[{{instructionsField.handle}}]"></textarea>
{% endif %}
```

Both of these fields work like normal fields added to address/cart field layout. They also have additional validation rules used for maximum length of content, which is especially important for delivery instructions. 

Note that admin can still add delivery instructions manually, even if proper field is not added to order field layout. Instructions can be added using Eayship interface on order page, when creating parcel. If instructions field is added, this interface can be used to override it.

## Display parcels tracking links

Place this on order page. Parcels that has just been created and don't have labels printed out yet cannot be tracked.

```
{% for parcel in order.dpdEasyship.parcels %}
    {% if parcel.getTrackingUrl() %}
        <a href="{{ parcel.getTrackingUrl() }}" target="_blank">
            {{ parcel.number }}
        </a>
    {% endif %}
{% endfor %}
```

## Sender and delivery addresses

Delivery address is taken from shipping address set in the order. 

Sender address is optional. To use it, you need to create at least one inventory location in commerce/inventory/locations. In plugin settings then you can set it as default one which will be used when sending. This default selection can be later override in DPD interface on order page.

Remember that address field layout **needs to have "Full name" native field assigned**. For both sender and delivery addresses, "Address Line 1" will be used for street and "Address Line 2" for house number.

For delivery address, if you enter "Organisation" and "Full name", organization is used for "Receiver company or personal name" and full name for "Receiver additional name". If you enter just full name, it will be used for "Receiver company or personal name".

Note that you cannot enter any postal code into addresses - API accepts only postal codes with the specific formats. For Slovenia only 4 digits codes are accepted and for Croatia 5 digits (Croatia also seems to have some additional validation rules and its best to just find some existing postal code).

## Parcel reference number

For "Customer’s parcel reference" and "Customer’s COD reference" (if cash on delivery is used), order number is used. Not to be confused with order id.

## Parcel types

When selecting which shipping methods should have Easyship option in plugin settings, we can select parcel types. There are many parcel types allowed in the api, however plugin allows only for two at the moment - Classic parcel and Classic COD (cash ond delivery) parcel.

Additional types of parcels would require adding additional options in the plugin interface.

## Cash on delivery

Cash on delivery uses currency of destination country.

IT SHOULD NOT BE USED IF SHOP HAS DIFFERENT CURRENCY USED ON FRONTEND, AS THERE IS NO RE-CALCULATION LOGIC BETWEEN DIFFERENT CURRENCIES.

There are three of types of cash of delivery that can be set in "Cash On Delivery (COD) type" plugin settings. One of them, "All" in case of multiple parcels per order seems to place same COD amount on every parcel, which in theory could multiply amount of received money. Select this type with caution.

## Saved parcel data

After the parcels are created, it is still possible to edit order. However, this will not edit parcel data, as it was already sent to API. 

In theory, admin user could be under impression that editing order address will change address in already created parcels. To avoid misunderstandings, parcels info is saved at the moment of creation in the database and can be checked by clicking "Show details" in the shipping interface. Most of parcel properties are empty, as plugin does not use all possible settings.

## Update parcels queue job

Parcels status can be updated by clicking "Update parcels status" button in shipping interface on order page or by selecting multiple parcels on orders list and selecting "update parcels status" option. This will update parcels status immediately.

You can also update ALL parcels status using queue job. Thanks to queue job, system will not be blocked by large amount of API requests running all at once. 

This can be triggered in Utilities/Dpd easyship or by using console command. When running queue job, plugin will ignore orders missing Easyship data or that have order statuses defined in "Order status that will be set when parcels status will be updated to 'delivered' status." plugin setting.

Console command for triggering upating of parcel status:

```
php craft dpd-easy-ship/parcels/update-parcels-statuses
```

## Updating order status

Order status can be updated automatically when parcel status is updated and parcels "DELIVERED" status is set. This can be set in plugin "Order status that will be set when parcels status will be updated to delivered status". Orders with that status will be ignored when running parcels update queue job.
