<?php

/**
 * @api {get} /api_customer/product/productList Danh sách tất cả product chưa lọc theo category_id 
 * @apiName getProductList
 * @apiGroup Product
 *
 * @apiParam {Number} order_type kiểu order(có thể không truyền)
 * @apiParam {Number} location_id id của location(có thể không truyền)
 * @apiParam {Number} delivery_address địa chỉ nhận hàng(có thể không truyền)
 *
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
{
    "success": true,
    "response_code": 1000,
    "data": {
        "0": {
            "category_id": 18,
            "category_name": "Espresso",
            "special_tag": "Món được ưa thích",
            "list_product": {
                "0": {
                    "product_id": 19,
                    "product_name": "Espresso Đá",
                    "product_short_desc": "",
                    "product_description": "Espresso Đá",
                    "product_base_price": 45000,
                    "product_sale_price": 45000,
                    "product_image_thumbnail": "/uploads/products/19/avatar/espresso_master.jpg"
                },
                "1": {
                    "product_id": 20,
                    "product_name": "Espresso Nóng",
                    "product_short_desc": "",
                    "product_description": "Espresso Nóng",
                    "product_base_price": 40000,
                    "product_sale_price": 40000,
                    "product_image_thumbnail": "/uploads/products/20/avatar/espresso_master.jpg"
                },
                "product_count": 2
            }
        },
        "category_count": 1
    },
    "message": "success"
}
*/

/**
 * @api {get} /api_customer/product/productList Danh sách tất cả product lọc theo category_id
 * @apiName getProductListByCategory
 * @apiGroup Product
 *
 * @apiParam {Number} order_type kiểu order(có thể không truyền)
 * @apiParam {Number} location_id id của location(có thể không truyền)
 * @apiParam {Number} delivery_address địa chỉ nhận hàng(có thể không truyền)
 * @apiParam {Number} category_id id của category(required)
 *
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
{
    "success": true,
    "response_code": 1000,
    "data": {
        "0": {
            "category_id": 18,
            "category_name": "Espresso",
            "special_tag": "Món được ưa thích",
            "list_product": {
                "0": {
                    "product_id": 19,
                    "product_name": "Espresso Đá",
                    "product_short_desc": "",
                    "product_description": "Espresso Đá",
                    "product_base_price": 45000,
                    "product_sale_price": 45000,
                    "product_image_thumbnail": "/uploads/products/19/avatar/espresso_master.jpg"
                },
                "1": {
                    "product_id": 20,
                    "product_name": "Espresso Nóng",
                    "product_short_desc": "",
                    "product_description": "Espresso Nóng",
                    "product_base_price": 40000,
                    "product_sale_price": 40000,
                    "product_image_thumbnail": "/uploads/products/20/avatar/espresso_master.jpg"
                },
                "product_count": 2
            }
        },
        "category_count": 1
    },
    "message": "success"
}
*/

public function getList(Request $request)
{

}
/**
 * @api {get} /api_customer/product/productDetail chi tiết của 1 product 
 * @apiName getProductDetail
 * @apiGroup Product
 *
 * @apiParam {Number} product_id id của product(required)
 *
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
{
    "success": true,
    "response_code": 1000,
    "data": {
        "product_id": 7,
        "product_name": "Caramel Macchiato Đá",
        "product_short_desc": "",
        "product_description": "Caramel Macchiato Đá",
        "product_base_price": 50000,
        "product_sale_price": 50000,
        "product_image_thumbnail": "/uploads/products/7/avatar/caramel_macchiato.jpg",
        "cover_list": [
            "/uploads/products/7/images/caramel_macchiato.jpg"
        ],
        "group_option": [
            {
                "group_option_id": 1,
                "group_option_name": "Độ ngọt",
                "group_option_product_type": 2,
                "group_option_product_type_show": 1,
                "option_list": [
                    {
                        "option_id": 1,
                        "option_name": "Nhiều"
                    },
                    {
                        "option_id": 2,
                        "option_name": "ít"
                    },
                    {
                        "option_id": 3,
                        "option_name": "Không có"
                    }
                ]
            },
            {
                "group_option_id": 2,
                "group_option_name": "Độ chua",
                "group_option_product_type": 1,
                "group_option_product_type_show": 2,
                "option_list": [
                    {
                        "option_id": 4,
                        "option_name": "Nhiều"
                    },
                    {
                        "option_id": 5,
                        "option_name": "ít"
                    },
                    {
                        "option_id": 6,
                        "option_name": "Không có"
                    }
                ]
            }
        ],
        "size": [
            {
                "size_id": 1,
                "size_price": 50000,
                "size_name": "S moi",
                "weight_number": null
            },
            {
                "size_id": 2,
                "size_price": 50000,
                "size_name": "M",
                "weight_number": null
            },
            {
                "size_id": 3,
                "size_price": 50000,
                "size_name": "L",
                "weight_number": null
            }
        ],
        "product_topping_own": [],
        "product_topping_by_category": [
            {
                "topping_price": "10000",
                "topping_name": "Espresso (1shot)",
                "topping_id": 1
            }
        ],
        "product_tags": []
    },
    "message": "Detail success"
}
*/
public function getDetail(Request $request)
{

}
