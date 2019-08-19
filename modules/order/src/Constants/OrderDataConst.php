<?php

namespace APV\Order\Constants;

use APV\Base\Constants\BaseDataConst;

/**
 * Class OrderDataConst
 * @package APV\Table\Constants
 */
class OrderDataConst extends BaseDataConst
{
    const ERROR_CODE_NO_PERMISSION = [
        'code' => 914,
        'message' => 'You have no permission to access to this entity',
    ];
    const ERROR_CODE_UNCREATE_NEW = [
        'code' => 1100,
        'message' => 'Create categories error',
    ];
    const PAGINATE = 1;
    //status order
    const ORDER_STATUS_CANCEL = 0;
    const ORDER_STATUS_CREATED = 1;
    const ORDER_STATUS_CONFIRM_KITCHENT = 2;
    const ORDER_STATUS_CONFIRM_CASHIER = 3;
    const ORDER_STATUS_DELETE = 4;
    //order_type_id: 1:tai shop, 2: take away, 3: ship
    const ORDER_TYPE_AT_SHOP = 1;
    const ORDER_TYPE_TAKE_AWAY = 2;
    const ORDER_TYPE_SHIP = 3;
    const NO_ORDER = 'Have no order';
    const ORDER_NUMBER_WAITTING = 'number_waitting';

}
