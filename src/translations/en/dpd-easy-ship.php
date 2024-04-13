<?php
return [
    'API Login' => 'API Login',
    'API Password' => 'API Password',
    'API Country' => 'API Country',
    'Please enter login, password and country in the the EasyShip plugin settings' => 'Please enter login, password and country in the the EasyShip plugin settings',
    'DPD EasyShip - get parcel labels' => 'DPD EasyShip - get parcel labels',

    'DPD EasyShip - create parcels' => 'DPD EasyShip - create parcels',

    'Number of parcels' => 'Number of parcels',
    'Parcel weight (kg)' => 'Parcel weight (kg)',
    'Mandatory for PUDO parcels. Default value is calculated from the products weight.' => 'Mandatory for PUDO parcels. Default value is calculated from the products weight.',
    'Delivery instructions for courier' => 'Delivery instructions for courier',
    'The default value is taken from the proper field assigned to order, if one was set in the plugin settings.' => 'The default value is taken from the proper field assigned to order, if one was set in the plugin settings.',

    'DPD EasyShip' => 'DPD EasyShip',
    'Create parcels' => 'Create parcels',
    'Get parcel labels' => 'Get parcel labels',
    'Submit' => 'Submit',
    'Cancel' => 'Cancel',
    'Remove parcels' => 'Remove parcels',
    'Are you sure you want to remove the parcels from the Order?' => 'Are you sure you want to remove the parcels from the Order?',
    'Update parcels status' => 'Update parcels status',
    'Parcels' => 'Parcels',

    'Phone number field' => 'Phone number field',
    'Select one of the plain text fields assigned to the address model. Value of this field will be used for the parcels generation request.' => 'Select one of the plain text fields assigned to the address model. Value of this field will be used for the parcels generation request.',
    'Select' => 'Select',
    'Are you sure you want to create DPD EasyShip parcels for the selected orders? Default settings will be used for the each parcel.' => 'Are you sure you want to create DPD EasyShip parcels for the selected orders? Default settings will be used for the each parcel.',
    'Could not create DPD EasyShip parcels for the all selected orders. Errors:' => 'Could not create DPD EasyShip parcels for the all selected orders. Errors:',
    'Could not create DPD EasyShip parcels for the all selected orders.' => 'Could not create DPD EasyShip parcels for the all selected orders.',
    'Parcels already exist for this order.' => 'Parcels already exist for this order.',
    'DPD EasyShip parcels status updated for the selected orders.' => 'DPD EasyShip parcels status updated for the selected orders.',
    'DPD EasyShip - update parcels status' => 'DPD EasyShip - update parcels status',
    'DPD EasyShip parcels status' => 'DPD EasyShip parcels status',

    'Shipping methods with Dpd EasyShip integration enabled' => 'Shipping methods with Dpd EasyShip integration enabled',
    'Shipping method' => 'Shipping method',
    'Parcel type' => 'Parcel type',
    'Add the shipping method' => 'Add the shipping method',
    'You need to finish editing the order, before using Ddp EasyShip.' => 'You need to finish editing the order, before using Ddp EasyShip.',
    'Please note that DPD EasyShip is disabled for the current shipping method of this order. You can still access DPD EasyShip functionality because this order already has parcels assigned.' => 'Please note that DPD EasyShip is disabled for the current shipping method of this order. You can still access DPD EasyShip functionality because this order already has parcels assigned.',

//    'Warning! Parcels were created with Cash On Delivery (COD) option enabled, but current setting for this shipping method has this option disabled.' => 'Warning! Parcels were created with Cash On Delivery (COD) option enabled, but current setting for this shipping method has this option disabled.',
//    'Warning! Parcels were created with Cash On Delivery (COD) option disabled, but current setting for this shipping method has this option enabled.' => 'Warning! Parcels were created with Cash On Delivery (COD) option disabled, but current setting for this shipping method has this option enabled.',
//    'Cash on delivery (COD) option is enabled for this shipping method.' => 'Cash on delivery (COD) option is enabled for this shipping method.',
//    'Warning! Parcels were created with different Cash On Delivery (COD) type setting than one currently assigned to this shipping method' => 'Warning! Parcels were created with different Cash On Delivery (COD) type setting than one currently assigned to this shipping method',

    'Amount of Cash On Delivery (COD) for this parcel(s) is different than total sum for this order.' => 'Amount of Cash On Delivery (COD) for this parcel(s) is different than total sum for this order.',

    'Average - the amount of each parcel will be the average amount of the total COD amount' => 'Average - the amount of each parcel will be the average amount of the total COD amount',
    'All - all parcels have the same amount which is the total COD amount' => 'All - all parcels have the same amount which is the total COD amount',
    'First only - only the first parcel will have the COD amount and the other parcels will be DPD Classic parcels' => 'First only - only the first parcel will have the COD amount and the other parcels will be DPD Classic parcels',
    'Cash On Delivery (COD) type' => 'Cash On Delivery (COD) type',
    'This setting is only used for the COD type parcels.' => 'This setting is only used for the COD type parcels.',
    'Cash On Delivery (COD)' => 'Cash On Delivery (COD)',

    'Delivery instructions for courier field' => 'Delivery instructions for courier field',
    'Optional. One of the plain text fields assigned to the Order model. Value of this field will be used for the parcels generation request.' => 'Optional. One of the plain text fields assigned to the Order model. Value of this field will be used for the parcels generation request.',

    // parcel types
    'DPD Classic' => 'DPD Classic',
    'DPD Classic COD' => 'DPD Classic COD',
    'DPD Classic Document return' => 'DPD Classic Document return',
    'DPD Home (B2C)' => 'DPD Home (B2C)',
    'DPD Home COD' => 'DPD Home COD',
    'Exchange' => 'Exchange',
    'Tyre' => 'Tyre',
    'Tyre (B2C)' => 'Tyre (B2C)',
    'Parcel shop' => 'Parcel shop',
    'Pallet' => 'Pallet',
    'DPD Home COD with return label' => 'DPD Home COD with return label',


    'Show details' => 'Show details',
    'Hide details' => 'Hide details',

    // sender info
    'Sender contact info on the label' => 'Sender contact info on the label',
    'Default sender address' => 'Default sender address',
    'Select the location which address will be used as default sender address when creating parcels for orders. This setting can be overridden for the specific orders.' => 'Select the location which address will be used as default sender address when creating parcels for orders. This setting can be overridden for the specific orders.',
//    'You need to create at least one location' => 'You need to create at least one location',

    'Sender address' => 'Sender address',
    'Select the location which address will be used as the sender address for the parcels.' => 'Select the location which address will be used as the sender address for the parcels.',

    'Croatia' => 'Croatia',
    'Slovenia' => 'Slovenia',
    'Postal codes from Slovenia must be 4 digits long and postal codes from Croatia must be 5 digits long. Using different values will result in the DPD EasyShip API errors. This applies both to the sender address set in the plugin settings and the client shipping address set for the order.' => 'Postal codes from Slovenia must be 4 digits long and postal codes from Croatia must be 5 digits long. Using different values will result in the DPD EasyShip API errors. This applies both to the sender address set in the plugin settings and the client shipping address set for the order.',

    'Only Croatia and Slovenia are allowed in the client shipping address country field. Using other country values will result in the DPD EasyShip API errors. You can limit selectable countries in commerce store settings, under "Country List" field. You can also set default country for addresses using "defaultCountryCode" option in the  config.php settings file.' => 'Only Croatia and Slovenia are allowed in the client shipping address country field. Using other country values will result in the DPD EasyShip API errors. You can limit selectable countries in commerce store settings, under "Country List" field. You can also set default country for addresses using "defaultCountryCode" option in the  config.php settings file.',

    // todo - add all parcel creation request properties translations

    'Order status that will be set when parcels status will be updated to "delivered" status.' => 'Order status that will be set when parcels status will be updated to "delivered" status.',
    'Cannot remove parcels - only parcels with "CREATED" and "PRINTED" status can be removed.' => 'Cannot remove parcels - only parcels with "CREATED" and "PRINTED" status can be removed.',
    'Removing...' => 'Removing...',

//    'Open parcel labels PDF in the new tab or download it immediately' => 'Open parcel labels PDF in the new tab or download it immediately',
//    'Open in the new tab' => 'Open in the new tab',
//    'Download' => 'Download',

    'Updating DPD EasyShip parcels statuses' => 'Updating DPD EasyShip parcels statuses',
    'Update parcels statuses' => 'Update parcels statuses',

    'Only Croatia or Slovenia are allowed for sender address.' => 'Only Croatia or Slovenia are allowed for sender address.',
    'Only Croatia or Slovenia are allowed for delivery address.' => 'Only Croatia or Slovenia are allowed for delivery address.',
    'DPD EasyShip delete parcels request' => 'DPD EasyShip delete parcels request',
    'DPD EasyShip cancel parcels request' => 'DPD EasyShip cancel parcels request',
    'DPD EasyShip update parcels queue job started.' => 'DPD EasyShip update parcels queue job started.',
    'This order shipping method has DPD EasyShip enabled, but no DPD EasyShip field is assigned to order field layout.' => 'This order shipping method has DPD EasyShip enabled, but no DPD EasyShip field is assigned to order field layout.',
    'Creating parcels of the type:' => 'Creating parcels of the type:',
    'Remember that editing order settings after parcels were already created, will not influence parcels settings.' => 'Remember that editing order settings after parcels were already created, will not influence parcels settings.',
    '(in the currency of the recipient country)' => '(in the currency of the recipient country)',
    'status' => 'status',
    ];