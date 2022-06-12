<?php

namespace App\Dictionary;

class ConfigurationCodeDictionary extends Dictionary
{
    public const OFFLINE_GATEWAY_AVAILABILITY = 'OFFLINE_GATEWAY_AVAILABILITY';
    public const CPG_GATEWAY_AVAILABILITY = 'CPG_GATEWAY_AVAILABILITY';
    public const HAMRAH_CARD_GATEWAY_AVAILABILITY = 'HAMRAH_CARD_GATEWAY_AVAILABILITY';
    public const DEFAULT_ONLINE_GATEWAY = 'DEFAULT_ONLINE_GATEWAY';
    public const DEFAULT_COD_GATEWAY = 'DEFAULT_COD_GATEWAY';
    public const CHECK_INITIAL_INVENTORY_STATUS = 'CHECK_INITIAL_INVENTORY_STATUS';
    public const AUTO_CONFIRM_ORDER = 'AUTO_CONFIRM_ORDER';
    public const CUSTOMER_INVOICE_EXCLUDED_SELLERS = 'CUSTOMER_INVOICE_EXCLUDED_SELLERS';
    public const CUSTOMER_INVOICE_EXCLUDED_CATEGORIES = 'CUSTOMER_INVOICE_EXCLUDED_CATEGORIES';
    public const SELLER_SEARCH_EXCLUDED_CATEGORIES = 'SELLER_SEARCH_EXCLUDED_CATEGORIES';
    public const WAREHOUSE_START_TIME = 'WAREHOUSE_START_TIME';
    public const WAREHOUSE_PROCESSING_DURATION_IN_HOUR = 'WAREHOUSE_PROCESSING_DURATION_IN_HOUR';
    public const WAREHOUSE_END_TIME = 'WAREHOUSE_END_TIME';
    public const PARTIAL_SHIPMENT_SELECTABLE_DAYS_COUNT = 'PARTIAL_SHIPMENT_SELECTABLE_DAYS_COUNT';
    public const RATE_AND_REVIEW_SMS_APOLOGY_ID = 'RATE_AND_REVIEW_SMS_APOLOGY_ID';
    public const ON_SALE_PRODUCTS = 'ON_SALE_PRODUCTS';
    public const ON_SALE_INVENTORY = 'ON_SALE_INVENTORY';
    public const WAITING_FOR_SUPPLY_ORDER_APOLOGY_PROMOTION_ID = 'WAITING_FOR_SUPPLY_ORDER_APOLOGY_PROMOTION_ID';
    public const ABANDONED_CART_NOTIFICATION = 'ABANDONED_CART_NOTIFICATION';
    public const MINIMUM_CART = 'MINIMUM_CART';
    public const SELLER_SCORE_LEVELS = 'SELLER_SCORE_LEVELS';
}
