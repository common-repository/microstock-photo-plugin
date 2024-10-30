<?php

/**
 * Constants for predefined Deposit API commands and parameters.
 *
 * @copyright   Copyright (c) 2010 DepositPhotos Inc.
 */
class RpcParams
{
    const APIKEY        = 'dp_apikey';
    const COMMAND       = 'dp_command';
    const SESSION_ID    = 'dp_session_id';

    const SEARCH_CMD            = 'search';
    const GET_CATEGORIES_CMD    = 'getCategoriesList';
    const GET_MEDIA_DATA_CMD    = 'getMediaData';
    const GET_TAG_CLOUD_CMD     = 'getTagCloud';
    const LOGIN_CMD             = 'login';
    const LOGINASUSER_CMD       = 'loginAsUser';
    const GET_MEDIA_CMD         = 'getMedia';
    const CREATE_SUBACCOUNT_CMD = 'createSubaccount';
    const DELETE_SUBACCOUNT_CMD = 'deleteSubaccount';
    const UPDATE_SUBACCOUNT_CMD = 'updateSubaccount';
    const GET_SUBACCOUNTS_CMD   = 'getSubaccounts';
    const GET_SUBACCOUNT_DATA_CMD   = 'getSubaccountData';
    const GET_SUBACCOUNT_PURCHASES_CMD  = 'getSubaccountPurchases';
    const GET_LICENSE_CMD       = 'getLicense';
    const ADD_TO_CART_CMD       = 'addToCart';
    const DELETE_FROM_CART_CMD  = 'deleteFromCart';
    const GET_CART_ITEMS_CMD    = 'getCartItems';
    const CLEAR_CART_CMD        = 'clearCart';
    const GET_INFO_CMD          = 'getInfo';
    const AVAILABLE_FUNDS_CMD = 'availableFunds';

    const SEARCH_QUERY  = 'dp_search_query';
    const SEARCH_SORT   = 'dp_search_sort';
    const SEARCH_LIMIT  = 'dp_search_limit';
    const SEARCH_OFFSET = 'dp_search_offset';
    const SEARCH_CATEGORIES = 'dp_search_categories';
    const SEARCH_COLOR  = 'dp_search_color';
    const SEARCH_NUDITY = 'dp_search_nudity';
    const SEARCH_EXTENDED   = 'dp_search_extended';
    const SEARCH_EXCLUSIVE  = 'dp_search_exclusive';
    const SEARCH_USER   = 'dp_search_user';
    const SEARCH_USERNAME   = 'dp_search_username';
    const SEARCH_DATE1  = 'dp_search_date1';
    const SEARCH_DATE2  = 'dp_search_date2';
    const SEARCH_ORIENTATION= 'dp_search_orientation';
    const SEARCH_IMAGESIZE  = 'dp_search_imagesize';
    const SEARCH_VECTOR = 'dp_search_vector';
    const SEARCH_PHOTO  = 'dp_search_photo';
    const SEARCH_EXCLUDE_KEYWORDS = 'dp_search_exclude_keywords';
    const SEARCH_EXCLUDE_AUTHORS = 'dp_search_exclude_authors';


    const MEDIA_ID          = 'dp_media_id';
    const MEDIA_OPTION      = 'dp_media_option';
    const MEDIA_LICENSE     = 'dp_media_license';
    const MEDIA_PURCHASE_CURRENCY = 'dp_purchase_currency';

    const LOGIN_USER    = 'dp_login_user';
    const LOGIN_PASSWORD= 'dp_login_password';

    const SUBACC_ID     = 'dp_subaccount_id';
    const SUBACC_EMAIL  = 'dp_subaccount_email';
    const SUBACC_FNAME  = 'dp_subaccount_fname';
    const SUBACC_LNAME  = 'dp_subaccount_lname';
    const SUBACC_COMPANY= 'dp_subaccount_company';
    const SUBACC_LIMIT  = 'dp_subaccount_limit';
    const SUBACC_OFFSET = 'dp_subaccount_offset';
    const SUBACC_LICENSE_ID = 'dp_subaccount_license_id';

    const ORIENT_ALL        = 'all';
    const ORIENT_VERTICAL   = 'vertical';
    const ORIENT_HORIZONTAL = 'horizontal';
    const ORIENT_SQUARE     = 'square';

    const SIZE_ALL      = 'all';
    const SIZE_XSMALL   = 'xs';
    const SIZE_SMALL    = 's';
    const SIZE_MEDIUM   = 'm';
    const SIZE_LARGE    = 'l';
    const SIZE_XLARGE   = 'xl';
    const SIZE_XXLARGE  = 'xxl';
    const SIZE_XXXLARGE = 'xxxl';
    const SIZE_VECTOR   = 'vector';

    const EXTENDED_1    = 'el1';
    const EXTENDED_2    = 'el2';
    const EXTENDED_3    = 'el3';
    const EXTENDED_4    = 'el4';
    const EXTENDED_5    = 'el5';

    const LICENSE_STANDART  = 'standard';
    const LICENSE_EXTENDED  = 'extended';

    const CURRENCY_CREDITS = 'credits';
    const CURRENCY_SUBSCRIPTION = 'subscription';
    const CURRENCY_BONUS = 'bonus';
}

?>