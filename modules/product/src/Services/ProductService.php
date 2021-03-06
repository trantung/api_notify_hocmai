<?php

namespace APV\Product\Services;

use APV\Category\Models\Category;
use APV\Topping\Models\Topping;
use APV\Product\Models\Product;
use APV\Product\Models\GroupOption;
use APV\Product\Models\GroupOptionProduct;
use APV\Product\Models\Option;
use APV\Product\Models\OptionProduct;
use APV\Order\Constants\OrderDataConst;
use APV\Order\Models\Order;
use APV\Order\Models\OrderProduct;
use APV\Order\Models\OrderProductTopping;
use APV\Order\Models\OrderProductOption;
use APV\Topping\Models\ToppingCategory;
use APV\Product\Models\ProductTopping;
use APV\Product\Constants\ProductDataConst;
use APV\Product\Models\CommonImage;
use APV\Tag\Models\TagProduct;
use APV\Tag\Models\Tag;
use APV\Size\Models\SizeProduct;
use APV\Size\Models\Size;
use APV\Size\Models\SizeResource;
use APV\Size\Models\Step;
use APV\Material\Models\Material;
use APV\Base\Services\BaseService;
use APV\Category\Services\CategoryService;
use APV\Promotion\Models\Voucher;
use APV\Customer\Models\Customer;
use APV\Customer\Models\CustomerAddress;

/**
 * Class ProductService
 * @package APV\Product\Services
 * @property CategoryService categoryService
 */

class ProductService extends BaseService
{
   public function __construct(Product $model)
    {
        parent::__construct($model);
        $categoryService = new CategoryService(new Category);
        $this->categoryService = $categoryService;
    }

    public function create($input)
    {
        $file = request()->file('avatar');
        if (!$file) {
            return false;
        }
        $productId = Product::create($input)->id;
        if (!$productId) {
            return false;
        }
        //update barcode
        $productBarcode = getBarCodeProduct($productId);
        //upload avatar: todo
        $fileNameImage = $file->getClientOriginalName();
        $file->move(public_path("/uploads/products/" . $productId . '/avatar/'), $fileNameImage);
        $imageUrl = '/uploads/products/' . $productId . '/avatar/' . $fileNameImage;
        Product::where('id', $productId)->update(['avatar' => $imageUrl, 'barcode' => $productBarcode]);
        //upload nhieu anh: todo
        if (is_countable($input['images']) && count($input['images']) > 0) {
            $this->postCreateImages($productId, $input['images']);
        }
        return $productId;
    }

    public function getList()
    {
        // $myTime = '19:30';
        // if (date('H:i') == date('H:i', strtotime($myTime))) {
        //     // do something
        // }
        // $shop = Shop::find(1);
        // $openTime = $shop->open_time;
        // $closeTime = $shop->close_time;
        $products = Product::all();
        // foreach ($products as $key => $product) {
        //     if ($product->open_time) {
        //         # code...
        //     }
        // }
        return $products->toArray();
    }

    public function getCategoriesByProduct($productId)
    {
        $data = [];
        $product = Product::find($productId);
        $categoryId = $product->category_id;
        $category = Category::find($categoryId);
        if (!$categoryId) {
            $data['category_id'] = '';
            $data['category_name'] = '';
            return $data;
        }
        $data['category_id'] = $categoryId;
        $data['category_name'] = $category->name;
        return $data;
    }

    public function getTagByProduct($productId)
    {
        $listTagId = TagProduct::where('product_id')->pluck('tag_id');
        $data = [];
        foreach ($listTagId as $key => $tagId) {
            $tag = Tag::find($tagId);
            $data[$key]['id'] = $tagId;
            $data[$key]['name'] = $tag->name;
        }
        return $data;
    }
    
    public function getSizeProductDetail($productId, $sizeId, $field)
    {
        $data = SizeProduct::where('product_id', $productId)->where('size_id', $sizeId)->first();
        if (!$data) {
            return null;
        }
        return $data->$field;
    }

    public function getSizeProduct($productId, $material = null)
    {
        $listSizeId = SizeProduct::where('product_id', $productId)->pluck('size_id');
        $listSize = Size::whereIn('id', $listSizeId)->get();
        $data = [];
        foreach ($listSize as $key => $value) {
            $data[$key]['size_id'] = $sizeId = $value->id;
            $data[$key]['size_price'] = (int)$this->getSizeProductDetail($productId, $sizeId, 'price');
            $data[$key]['size_name'] = $value->name;
            $data[$key]['weight_number'] = $this->getSizeProductDetail($productId, $sizeId, 'weight_number');
            if ($material) {
                $data[$key]['product_base_price'] = $this->getBasePriceSizeProduct($productId, $sizeId);
                $data[$key]['product_sale_price'] = $this->getPromotionPriceProductBySize($productId, $sizeId);
            }
            if ($material == null) {
                $data[$key]['material'] = $this->getMaterialProduct($productId, $value->id);
            }
        }
        return $data;
    }

    public function getStep($productId, $sizeId, $materialId)
    {
        $data = [];
        $listStep = Step::where('product_id', $productId)->where('size_id', $sizeId)
            ->where('material_id', $materialId)->get();
        // $data = new CommonStep();
        foreach ($listStep as $key => $step) {
            $stepQuantity = $step->quantity;
            $data[$stepQuantity] = [
                'step_name' => $step->name,
                'step_id' => $step->id, 
                'step_quantity' => $step->quantity, 
            ];
        }
        return $data;
    }
    public function getMaterialProduct($productId, $sizeId)
    {
        $data = [];
        $materialIds = SizeResource::where('product_id', $productId)->where('size_id', $sizeId)->pluck('material_id');
        $materialList = Material::whereIn('id', $materialIds)->get();
        foreach ($materialList as $key => $value) {
            $data[$key]['material_name'] = $value->name;
            $data[$key]['material_id'] = $value->id;
            $data[$key]['size_product_material_detail'] = $this->getStep($productId, $sizeId, $value->id);
        }
        return $data;
    }

    public function getDetail($productId, $field = null)
    {
        $product = Product::find($productId);
        if (!$product) {
            return false;
        }
        if ($field) {
            return $product->$field;
        }
        $data = $product->toArray();
        $data['price_origin'] = (int) $product->price_origin;
        $data['price_pay'] = (int) $product->price_pay;
        $data['categories'] = $this->getCategoriesByProduct($productId);
        $data['product_images'] = $this->getProductImages($productId);
        $data['product_topping_own'] = $this->getToppingOwn($product);
        $data['product_topping_by_category'] = $this->getToppingByCategory($product);
        $data['product_tags'] = $this->getTagByProduct($productId);
        $data['product_size'] = $this->getSizeProduct($productId);
        return $data;
    }
    public function getToppingOwn($product)
    {
        $result = [];
        $data = ProductTopping::where('product_id', $product->id)->get();
        foreach ($data as $key => $value) {
            $result[$key]['topping_name'] = $result[$key]['topping_price'] = '';
            if ($topping = Topping::find($value->topping_id)) {
                $result[$key]['topping_id'] = $topping->id;
                $result[$key]['topping_name'] = $topping->name;
                $result[$key]['topping_price'] = $topping->price;
            }
        }
        return $result;
    }
    public function getToppingByCategory($product)
    {
        $categoryId = $product->category_id;
        $result = [];
        $toppingCategories = ToppingCategory::where('category_id', $categoryId)->get();
        foreach ($toppingCategories as $key => $value) {
            $result[$key]['topping_name'] = $result[$key]['topping_price'] = '';
            if ($topping = Topping::find($value->topping_id)) {
                $result[$key]['topping_id'] = $topping->id;
                $result[$key]['topping_name'] = $topping->name;
                $result[$key]['topping_price'] = $topping->price;
            }
        }
        return $result;
    }

    public function edit($productId, $input)
    {
        $product = Product::find($productId);
        if (!$product) {
            return false;
        }
        $file = request()->file('avatar');
        if ($file) {
            $fileNameImage = $file->getClientOriginalName();
            $file->move(public_path("/uploads/products/" . $productId . '/avatar/'), $fileNameImage);
            $imageUrl = '/uploads/products/' . $productId . '/avatar/' . $fileNameImage;
            $input['avatar'] = $imageUrl;
        }
        $product->update($input);

        if (count($input['images']) > 0) {
            $this->postUpdateImages($productId, $input['images']);
        }

        return true;
    }

    public function delete($productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return false;
        }
        //xoa SizeResource, SizeProduct, TagProduct, ProductTopping, Step, CommonImage
        Step::where('product_id', $productId)->delete();
        SizeResource::where('product_id', $productId)->delete();
        SizeProduct::where('product_id', $productId)->delete();
        TagProduct::where('product_id', $productId)->delete();
        ProductTopping::where('product_id', $productId)->delete();
        CommonImage::where('model_name', 'Product')->where('model_id', $productId)->delete();
        Product::destroy($productId);
        return true;
    }

    public function updateProductTopping($productToppingId, $input)
    {
        $productTopping = ProductTopping::find($productToppingId);
        $productId = $productTopping->product_id;
        Topping::destroy($productTopping->topping_id);
        ProductTopping::destroy($productToppingId);
        $this->createProductTopping($productId, $input);
        return true;
    }

    public function createProductTopping($productId, $input)
    {
        $toppingId = Topping::create($input)->id;
        ProductTopping::create([
            'product_id' => $productId, 
            'topping_id' => $toppingId,
            'source' => 0
        ]);
        return true;
    }

    public function getProductImages($productId)
    {
        $res = [];
        $data = CommonImage::where('model_name', 'Product')->where('model_id', $productId)->pluck('image_url');
        foreach ($data as $key => $value) {
            $res[] = url($value);
        }
        return $res;
    }

    public function postCreateImages($productId, $images)
    {

        foreach ($images as $key => $value) {
            $fileNameImage = $value->getClientOriginalName();
            $value->move(public_path("/uploads/products/" . $productId . '/images/'), $fileNameImage);
            $imageUrl = '/uploads/products/' . $productId . '/images/' . $fileNameImage;
            CommonImage::create(['model_id' => $productId, 'model_name' => 'Product', 'image_url' => $imageUrl]);
        }
    }

    public function postUpdateImages($productId, $images)
    {
        CommonImage::where('model_id', $productId)->where('model_name', 'Product')->delete();
        foreach ($images as $key => $value) {
            $fileNameImage = $value->getClientOriginalName();
            $value->move(public_path("/uploads/products/" . $productId . '/images/'), $fileNameImage);
            $imageUrl = '/uploads/products/' . $productId . '/images/' . $fileNameImage;
            CommonImage::create(['model_id' => $productId, 'model_name' => 'Product', 'image_url' => $imageUrl]);
        }
    }

    public function searchProduct($input)
    {
        //product_id, product_name, category_id,
        $data = Product::whereNull('deleted_at');

        return $data;
    }

    public function getNameCategory($categoryId)
    {
        $cate = Category::find($categoryId);
        if ($cate) {
            return $cate->name;
        }
        return '';
    }
    public function getFieldProductId($productId, $field)
    {
        $product = Product::find($productId);
        if (!$product) {
            return null;
        }
        return $product->$field;
    }

    public function getSpecialTagByCate($categoryId)
    {
        return 'M??n ???????c ??a th??ch';
    }

    public function getBasePriceSizeProduct($productId, $sizeId)
    {
        $basePriceSizeProduct = $this->getSizeProductDetail($productId, $sizeId, 'price');
        return $basePriceSizeProduct;
    }

    public function getBasePriceByProduct($product, $orderProductId = null)
    {
        if ($orderProductId) {
            $orderProduct = OrderProduct::find($orderProductId);
            if (!$orderProduct) {
                return 'order_product_id = ' . $orderProductId . ' kh??ng t??m th???y c??ng v???i product_id = ' . $product->id;
            }
            return $this->getBasePriceSizeProduct($product->id, $orderProduct->size_id);
        }
        $productSize = SizeProduct::where('product_id', $product->id)->where('active', ProductDataConst::PRODUCT_SIZE_PRICE_DEFAULT_ACTIVE)
            ->first();
        if (!$productSize) {
            return 'product_id = ' . $product->id . ' thieu product_size_price default cho order_product_id = ' . $orderProductId;
        }
        return $productSize->price;
    }

    public function getSalePriceByProduct($product, $orderProductId = null)
    {
        if ($orderProductId) {
            $orderProduct = OrderProduct::find($orderProductId);
            if (!$orderProduct) {
                return 'kh??ng c?? order_product_id = ' . $orderProductId;
            }
            return $this->getPromotionPriceProductBySize($product->id, $orderProduct->size_id);
        }
        $productSize = SizeProduct::where('product_id', $product->id)->where('active', ProductDataConst::PRODUCT_SIZE_PRICE_DEFAULT_ACTIVE)
            ->first();
        if (!$productSize) {
            return 'product_id = ' . $product->id . ' thieu product_size_price default cho order_product_id = ' . $orderProductId;
        }
        return $this->getPromotionPriceProductBySize($product->id, $productSize->size_id);
    }

    public function getInfoProduct($product, $orderProductId = null, $productToppingPrice = null)
    {
        $res = [];
        $res['product_id'] = $product->id;
        $res['product_name'] = $product->name;
        $res['product_short_desc'] = $product->short_desc;
        $res['product_description'] = $product->description;
        $toppingPrice = 0;
        if ($productToppingPrice) {
            $toppingPrice = $productToppingPrice;
        }
        $res['product_base_price'] = $toppingPrice + $this->getBasePriceByProduct($product, $orderProductId);
        $res['product_sale_price'] = $toppingPrice + $this->getSalePriceByProduct($product,$orderProductId);
        $res['product_image_thumbnail'] = url($product->avatar);
        $res['product_using_at'] = $product->using_at;
        return $res;
    }

    public function getProductByCategory($cateId, $usingAt = null)
    {
        $res = [];
        $listCategory = $this->categoryService->getListCategoryByRoot($cateId);
        if ($usingAt) {
            $arrayUsingAt = [$usingAt, ProductDataConst::PRODUCT_USING_AT_ALL];
            $listProduct = Product::whereIn('category_id', $listCategory)
                ->whereIn('using_at', $arrayUsingAt)
                ->get();
        } else {
            $listProduct = Product::whereIn('category_id', $listCategory)->get();
        }
        foreach ($listProduct as $key => $value) {
            $res[$key] = $this->getInfoProduct($value);
        }
        return $res;
    }

    public function customerGetList($input)
    {
        $usingAt = ProductDataConst::PRODUCT_USING_AT_SHOP;
        if (isset($input['using_at'])) {
            $usingAt = (int)$input['using_at'];
        }
        $res = [];
        $listCate = null;
        if (isset($input['category_id'])) {
            $arrayCate = $this->categoryService->getListCategoryByRoot($input['category_id']);
            $listCate = Category::whereIn('id', $arrayCate)->get();
        } else {
            $listCate = Category::all();
        }
        foreach ($listCate as $key => $value) {
            $res[$key]['category_id'] = $value->id;
            $res[$key]['category_name'] = $value->name;
            $res[$key]['special_tag'] = $this->getSpecialTagByCate($value);
            $res[$key]['list_product'] = $this->getProductByCategory($value->id, $usingAt);
        }
        $res = $this->formatArray2Array($res);
        return $res;
    }

    public function getVideoByProduct($product)
    {
        $res = ['https://www.youtube.com/watch?v=K_XmTiNojMg'];
        return $res;
    }

    public function getCoverListProduct($product)
    {
        $res = [];
        $data = $this->getProductImages($product->id);
        $dataVideo = $this->getVideoByProduct($product);
        $res = array_merge($data, $dataVideo);

        return $res;
    }

    public function getGroupOptionName($groupOptionId)
    {
        $res = GroupOption::find($groupOptionId);
        if ($res) {
            return $res->name;
        }
        return null;
    }

    public function getOptionProduct($product, $groupOptionId)
    {
        $res = [];
        $data = OptionProduct::where('product_id', $product->id)->pluck('option_id');
        $listOptions = Option::whereIn('id', $data)->where('group_option_id', $groupOptionId)->get();
        foreach ($listOptions as $key => $value) {
            $res[$key]['option_id'] = $value->id;
            $res[$key]['option_name'] = $value->name;
        }
        return $res;
    }

    public function getGroupOptionDetail($product, $orderProduct = null)
    {
        $res = [];
        $data = GroupOptionProduct::where('product_id', $product->id)->get();
        foreach ($data as $key => $value) {
            $res[$key]['group_option_id'] = $value->group_option_id;
            $res[$key]['group_option_name'] = $this->getGroupOptionName($value->group_option_id);
            $res[$key]['group_option_product_type'] = $value->type;
            $res[$key]['group_option_product_type_show'] = $value->type_show;
            if ($orderProduct) {
                $res[$key]['option_list'] = $this->getOptionProductOfOrderProduct($orderProduct, true);
            } else {
                $res[$key]['option_list'] = $this->getOptionProduct($product, $value->group_option_id);
            }
            
        }
        return $res;
    }

    public function getToppingByProduct($product)
    {
        $res = [];
        $data = ProductTopping::where('product_id', $product->id)->pluck('topping_id');
        $listTopping = Topping::whereIn('id', $data)->get();
        foreach ($listTopping as $key => $value) {
            $res[$key]['topping_id'] = $value->id;
            $res[$key]['topping_name'] = $value->name;
            $res[$key]['topping_price'] = $value->price;
        }
        return $res;
    }

    public function getListToppingProduct($product)
    {
        $data = array_merge($this->getToppingOwn($product), $this->getToppingByCategory($product));
        $check = [];
        foreach ($data as $key => $value) {
            if (!in_array($value['topping_id'], $check)) {
                $check[] = $value['topping_id'];
            } else {
                unset($data[$key]);
            }
        }
        return $data;
    }

    public function getInfoDetailProduct($product)
    {
        $res = $this->getInfoProduct($product);
        $res['cover_list'] = $this->getCoverListProduct($product);
        $res['group_option'] = $this->getGroupOptionDetail($product);
        $res['size'] = $this->getSizeProduct($product->id, true);
        $res['product_topping'] = $this->getListToppingProduct($product);
        $res['product_tags'] = $this->getTagByProduct($product->id);
        return $res;
    }
    public function customerGetDetail($input)
    {
        if (isset($input['product_id'])) {
            $productId = $input['product_id'];
            $product = Product::find($productId);
            if (!$product) {
                return [];
            }
            $res = $this->getInfoDetailProduct($product);
            return $res;
        }
        return [];
    }

    public function getInfoCustomer($input)
    {
        $res['customer_id'] = OrderDataConst::DEFAULT_CUSTOMER_ID;
        $res['customer_name'] = OrderDataConst::DEFAULT_CUSTOMER_NAME;
        $res['customer_phone'] = OrderDataConst::DEFAULT_CUSTOMER_PHONE;
        if (!isset($input['customer_id'])) {
            return $res;
        }
        $customer = Customer::find($input['customer_id']);
        if ($customer) {
            $res['customer_id'] = $customer->id;
            $res['customer_name'] = $customer->customer_name;
            $res['customer_phone'] = $customer->customer_phone;
        }
        return $res;
    }
    public function formatAddProductToCart($input)
    {
        $order = [];
        $customer = $this->getInfoCustomer($input);
        $order['status'] = OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED;
        $order = array_merge($order, $customer);
        $order['comment'] = $this->getValueDefault($input, 'comment', '');
        $order['created_by'] = $input['customer_id'];
        $order['ship_price'] = $this->getValueDefault($input, 'ship_price', 0);
        $order['ship_id'] = $this->getValueDefault($input, 'ship_id', 1);
        $order['total_product_price'] = $this->getValueDefault($input, 'total_product_price', 0);
        $order['total_topping_price'] = $this->getValueDefault($input, 'total_topping_price', 0);
        return $order;
    }
    public function getPriceAfterPromotion($price)
    {
        return $price;
    }

    public function getPromotionPriceProductBySize($productId, $sizeId)
    {
        $priceBeforePromotion = $this->getBasePriceSizeProduct($productId, $sizeId);
        $priceAfterPromotion = $this->getPriceAfterPromotion($priceBeforePromotion);
        return $priceAfterPromotion;
    }

    public function getToppingFromStr($strTopping)
    {
        $arrayTopping = explode(',', $strTopping);
        $data = Topping::whereIn('id', $arrayTopping)->pluck('price', 'id');
        return $data;
    }
    public function getOptionFromStr($strOption)
    {
        $arrayOption = explode(',', $strOption);
        $data = Option::whereIn('id', $arrayOption)->pluck('name', 'id');
        return $data;
    }
    public function getTotalPriceToppingByProduct($strTopping)
    {
        $total = 0;
        if ($strTopping == '') {
            return $total;
        }
        $arrayTopping = explode(',', $strTopping);
        foreach ($arrayTopping as $toppingId)
        {
            $topping = Topping::find($toppingId);
            if ($topping) {
                $total = $total + $topping->price;
            }
        }
        return $total;
    }

    public function getSizeOrderProduct($productId, $sizeId)
    {
        $listSize = $this->getSizeProduct($productId, true);
        foreach ($listSize as $size) {
            if ($size['size_id'] == $sizeId) {
                return $size;
            }
        }
        return false;
    }

    public function checkVoucher($voucherCode = null)
    {
        $default_promotion = 0;
        // neu voucher giam gia theo %. vi du: giam gia 10%
        if ($voucherCode) {
            $voucher = Voucher::where('code', $voucherCode)->first();
            if (!$voucher) {
                return false;
            }
            if ($voucher->percent_promotion > 0) {
                return [
                    'percent_promotion' => $voucher->percent_promotion,
                ];
            }
            if ($voucher->money_promotion > 0) {
                return [
                    'money_promotion' => $voucher->percent_promotion,
                ];
            }
        }
        return ['default_promotion' => $default_promotion];
    }

    public function getAmountOrderAfterPromotion($orderId, $voucherCode = null)
    {
        $voucher = $this->checkVoucher($voucherCode);
        $total_product_price = OrderProduct::where('order_id', $orderId)->where('status', OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED)->sum('total_price');
        if (!$voucher) {
            return $total_product_price;
        }
        if (isset($voucher['default_promotion'])) {
            return $total_product_price;
        }
        if (isset($voucher['percent_promotion'])) {
            $percentPromotion = $voucher['percent_promotion'];
            $promotionPrice = $total_product_price * $percentPromotion/100;
            $res = $total_product_price - $promotionPrice;
            return $res;
        }
        if (isset($voucher['money_promotion'])) {
            return $total_product_price - $voucher['money_promotion'];
        }
        return 0;
    }

    public function updateOrderCommon($orderId, $voucherCode = null)
    {
        $orderUp = Order::find($orderId);
        //t???ng ti???n ????n h??ng sau khi gi???m gi?? bao g???m c??? voucher
        $amount_after_promotion = $this->getAmountOrderAfterPromotion($orderId, $voucherCode);

        //t???ng ti???n ????n h??ng(order) tr?????c khi gi???m gi??
        $total_product_price = OrderProduct::where('order_id', $orderId)
            ->where('status', OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED)
            ->sum('total_before_promotion');

        //t???ng ti???n topping
        $total_price_topping = OrderProduct::where('order_id', $orderId)
            ->where('status', OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED)
            ->sum('total_price_topping');

        $orderUp->update([
            'total_product_price' => $total_product_price,
            'total_topping_price' => $total_price_topping,
            'amount_after_promotion' => $amount_after_promotion,
        ]);
        return true;
    }

    public function customerAddProduct($input, $orderEditId = null)
    {
        $res = [];
        $weightNumber = 1;
        $checkOrderExist = Order::where('customer_id', $input['customer_id'])->where('status', OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED)->first();
        if ($checkOrderExist) {
            $orderId = $checkOrderExist->id;
            $number = OrderProduct::where('order_id', $orderId)->orderBy('weight_number', 'desc')->first();
            $weightNumber = $number->weight_number + $weightNumber;
        } else {
            $order = $this->formatAddProductToCart($input);
            $orderId = Order::create($order)->id;
        }
        if ($orderEditId) {
            $orderId = $orderEditId;
            if (isset($input['weight_number'])) {
                $weightNumber = $input['weight_number'];
            }
        }
        $res['size'] = $this->getSizeOrderProduct($input['product_id'], $input['size_id']);
        //tao m???i record trong b???ng order_product
        //gi?? ti???n tr?????c khi gi???m gi??: order_product.product_price
        //gi?? ti???n sau khi gi???m gi??:order_product.promotion_price,
        //t???ng ti???n s???n ph???m ch??a c?? topping sau khi gi???m gi??: order_product.price
        //t???ng ti???n c???a s???n ph???m bao g???m c??? topping: order_product.total_price
        //t???ng ti???n c???a s???n ph???m bao g???m topping tr?????c khi gi???m gi??: order_product.total_before_promotion
        $baseProductSizePrice = $this->getBasePriceSizeProduct($input['product_id'], $input['size_id']);
        $productPriceAfterPromotion = $this->getPromotionPriceProductBySize($input['product_id'], $input['size_id']);
        $totalPriceAfterPromotion = $productPriceAfterPromotion * $input['product_quantity'];
        $totalPriceTopping = $input['product_quantity'] * $this->getTotalPriceToppingByProduct($input['topping']);
        $totalPrice = $totalPriceAfterPromotion + $totalPriceTopping;
        $totalBeforePromotion = $baseProductSizePrice * $input['product_quantity'] + $totalPriceTopping;
        // dd($totalBeforePromotion);
        $orderProduct['order_id'] = $orderId;
        $orderProduct['status'] = OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED;
        $orderProduct['customer_id'] = $input['customer_id'];
        $orderProduct['table_id'] = $this->getValueDefault($input, 'table_id', 1);
        $orderProduct['table_qr_code'] = $this->getValueDefault($input, 'table_qr_code', '');
        $orderProduct['level_id'] = $this->getValueDefault($input, 'level_id', 1);
        $orderProduct['ship_id'] = $this->getValueDefault($input, 'ship_id', 1);
        $orderProduct['product_id'] = $res['product_id'] = $input['product_id'];
        $orderProduct['quantity'] = $res['product_quantity'] = $input['product_quantity'];
        $orderProduct['size_id'] = $input['size_id'];
        $orderProduct['order_product_comment'] = $input['product_comment'];
        $orderProduct['product_price'] = $res['product_base_price'] = $baseProductSizePrice;
        $orderProduct['price'] = $totalPriceAfterPromotion;
        $orderProduct['promotion_price'] = $res['product_sale_price'] = $productPriceAfterPromotion;
        $orderProduct['total_before_promotion'] = $totalBeforePromotion;
        $orderProduct['total_price'] = $totalPrice;
        $orderProduct['total_price_topping'] = $totalPriceTopping;
        $orderProduct['weight_number'] = $weightNumber;
        $orderProductId = OrderProduct::create($orderProduct)->id;
        //update order cung voi amount_after_promotion, total_product_price, total_topping_price
        $this->updateOrderCommon($orderId);
        //tao moi record trong bang order_product_topping
        $listTopping = $this->getToppingFromStr($input['topping']);
        foreach ($listTopping as $toppingId => $toppingPrice) {
            OrderProductTopping::create([
                'order_product_id' => $orderProductId,
                'order_id' => $orderId,
                'product_id' => $input['product_id'],
                'topping_id' => $toppingId,
                'topping_price' => $toppingPrice,
            ]);
        }
        //tao moi record trong bang order_product_option
        $arrayOption = $this->getOptionFromStr($input['option']);
        $groupOption = [];
        foreach ($arrayOption as $optionId => $optionName) {
            $groupOption[$optionId]['option_id'] = $optionId;
            $groupOption[$optionId]['option_name'] = $optionName;
            OrderProductOption::create([
                'order_product_id' => $orderProductId,
                'product_id' => $input['product_id'],
                'order_id' => $orderId,
                'option_id' => $optionId,
            ]);
        }
        $res['group_option'] = $this->formatArray2Array($groupOption);
        $res['order_product_id'] = $orderProductId;
        return $res;
    }
    public function getFieldOfToppingById($toppingId, $field)
    {
        $res['topping_name'] = '';
        $data = Topping::find($toppingId);
        if (!$data) {
            return $res;
        }
        return $data->$field;
    }

    public function checkActive($data, $value)
    {
        if ($data == $value) {
            return true;
        }
        return false;
    }
    public function getSizeProductOfOrderProduct($orderProduct, $active = null)
    {
        $sizeId = $orderProduct->size_id;
        $size = Size::find($sizeId);
        $res = [];
        if (!$size) {
            return $res;
        }
        if ($active) {
            $sizeProduct = $this->getSizeProduct($orderProduct->product_id, true);
            foreach ($sizeProduct as $key => $value) {
                $res[$key]['size_id'] = $value['size_id'];
                $res[$key]['size_name'] = $value['size_name'];
                $res[$key]['size_price'] = $value['size_price'];
                $res[$key]['size_weight_number'] = $value['weight_number'];
                $res[$key]['product_base_price'] = $this->getBasePriceSizeProduct($orderProduct->product_id, $sizeId);
                $res[$key]['product_sale_price'] = $this->getPromotionPriceProductBySize($orderProduct->product_id, $sizeId);
                $res[$key]['active'] = $this->checkActive($value['size_id'], $sizeId);
            }
            return $res;
        }
        $res['size_id'] = $size->id;
        $res['size_name'] = $size->name;
        return $res;
    }

    public function getToppingProductOfOrderProduct($orderProduct, $active = null)
    {
        $res = [];
        if ($active) {
            // $listTopping = Topping::all();
            $product = Product::find($orderProduct->product_id);
            $listTopping = $this->getListToppingProduct($product);
            $listToppingActiveId = OrderProductTopping::where('order_product_id', $orderProduct->id)->pluck('topping_id');
            foreach ($listTopping as $key => $value) {
                $res[$key]['topping_id'] = $value['topping_id'];
                $res[$key]['topping_name'] = $value['topping_name'];
                $res[$key]['topping_price'] = $value['topping_price'];
                if (in_array($value['topping_id'], $listToppingActiveId->toArray())) {
                    $res[$key]['active'] = true;
                } else {
                    $res[$key]['active'] = false;
                }
            }
            return $res;
        }

        $data = OrderProductTopping::where('order_product_id', $orderProduct->id)->get();
        foreach ($data as $key => $value) {
            $res[$key]['topping_id'] = $value->topping_id;
            $res[$key]['topping_name'] = $this->getFieldOfToppingById($value->topping_id, 'name');
            $res[$key]['topping_price'] = $value->topping_price;
        }
        return $res;
    }

    public function getNameOption($optionId)
    {
        $option = Option::find($optionId);
        if (!$option) {
            return '';
        }
        return $option->name;
    }

    public function getOptionProductOfOrderProduct($orderProduct, $active = null)
    {
        $res = [];
        if ($active) {
            // $listTopping = Topping::all();
            $product = Product::find($orderProduct->product_id);
            $listOptions = Option::all();
            $listOptionsActiveId = OrderProductOption::where('order_product_id', $orderProduct->id)->pluck('option_id');
            foreach ($listOptions as $key => $value) {
                $res[$key]['option_id'] = $value->id;
                $res[$key]['option_name'] = $value->name;
                if (in_array($value->id, $listOptionsActiveId->toArray())) {
                    $res[$key]['active'] = true;
                } else {
                    $res[$key]['active'] = false;
                }
            }
            return $res;
        }
        $data = OrderProductOption::where('order_product_id', $orderProduct->id)->get();
        foreach ($data as $key => $value) {
            $res[$key]['option_id'] = $value->option_id;
            $res[$key]['option_name'] = $this->getNameOption($value->option_id);
        }
        return $res;
    }
    public function getStatusProductCanel($orderProductStatus)
    {
        if ($orderProductStatus == OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED) {
            return ProductDataConst::PRODUCT_CANCEL_BY_CUSTOMER_FALSE;
        }
        if ($orderProductStatus == OrderDataConst::ORDER_STATUS_CUSTOMER_CANCEL) {
            return ProductDataConst::PRODUCT_CANCEL_BY_CUSTOMER_TRUE;
        }
    }

    public function cartListProduct($input)
    {
        $customerToken = $input['customer_token'];
        $checkToken = $this->checkCustomerToken($customerToken);
        if (!$checkToken || !isset($input['customer_id'])) {
            return false;
        }
        $customerId = $input['customer_id'];
        $order = Order::where('customer_id', $customerId)->where('status', OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED)->first();
        if (!$order) {
            return false;
        }
        $orderProducts = OrderProduct::where('order_id', $order->id)
            ->orderBy('weight_number', 'asc')
            ->get();
        // getInfoDetailProduct
        // $data = Product::whereIn('id', $listProduct)->get();
        $res = [];
        foreach ($orderProducts as $key => $orderProduct) {
            $product = Product::find($orderProduct->product_id);
            $productToppingPrice = OrderProductTopping::where('product_id', $orderProduct->product_id)
                ->where('order_product_id', $orderProduct->id)
                ->sum('topping_price');
            if (!$product) {
                return false;
            }
            
            $res[$key] = $this->getInfoProduct($product, $orderProduct->id, $productToppingPrice);
            // $res[$key]['product_size_price'] = $this->getPromotionPriceProductBySize($orderProduct->product_id, $orderProduct->size_id);
            $res[$key]['order_product_id'] = $orderProduct->id;
            $res[$key]['product_id'] = $orderProduct->product_id;
            $res[$key]['product_cancel'] = $this->getStatusProductCanel($orderProduct->status);
            $res[$key]['product_quantity'] = $orderProduct->quantity;

            // $res[$key]['product_quantity'] = $orderProduct->quantity;
            //size
            $res[$key]['size'] = $this->getSizeProductOfOrderProduct($orderProduct);
            //topping
            $res[$key]['topping'] = $this->getToppingProductOfOrderProduct($orderProduct);
            //option
            $res[$key]['option'] = $this->getOptionProductOfOrderProduct($orderProduct);
        }
        // product_sale_price
        $result['list_product'] = $this->formatArray2Array($res);
        $result['total_price'] = OrderProduct::where('order_id', $order->id)
            ->where('status', OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED)
            ->sum('total_price');
        //t???ng ti???n tr?????c khi gi???m gi??
        $result['total_price_base'] = OrderProduct::where('order_id', $order->id)
            ->where('status', OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED)
            ->sum('total_before_promotion');
        return $result;

    }

    public function cartEditProduct($input)
    {
        $orderProductId = $input['order_product_id'];
        $productId = $input['product_id'];
        $orderProduct = OrderProduct::find($orderProductId);
        if (!$orderProduct) {
            return false;
        }
        $product = Product::find($productId);
        $res = $this->getInfoProduct($product);
        $res['order_product_id'] = $orderProduct->id;
        $res['cover_list'] = $this->getCoverListProduct($product);
        $res['product_tags'] = $this->getTagByProduct($product->id);
        $res['product_quantity'] = $orderProduct->quantity;
        $res['size'] = $this->getSizeProductOfOrderProduct($orderProduct, true);
        $res['product_topping'] = $this->getToppingProductOfOrderProduct($orderProduct, true);
        $res['group_option'] = $this->getGroupOptionDetail($product, $orderProduct);
        $res['product_comment'] = $orderProduct->order_product_comment;
        return $res;
    }

    public function commonDeleteProduct($orderProductId)
    {
        //xo?? b???ng order_product_option
        OrderProductOption::where('order_product_id', $orderProductId)->delete();
        //xo?? b???ng order_product_topping
        OrderProductTopping::where('order_product_id', $orderProductId)->delete();
        //xo?? b???ng order_product
        OrderProduct::destroy($orderProductId);
        //xoa san pham khoi gio hang
        return true;
    }

    public function cartUpdateProduct($input)
    {
        // product_id, product_quantity, product_comment
        // order_product_id, product_id(required), product_quantity, product_comment, topping, option, size
        if (!$input['customer_id']) {
            return false;
        }
        $res = [];
        $productId = $input['product_id'];
        $orderProductId = $input['order_product_id'];
        $orderProduct = OrderProduct::find($orderProductId);
        $orderId = $orderProduct->order_id;
        if (!$orderProduct) {
            return 'order_product_id = ' . $orderProductId . ' is wrong';
        }
        $input['weight_number'] = $orderProduct->weight_number;
        //ch??? update s??? l?????ng s???n ph???m. Param: customer_id, customer_token, order_product_id, product_id, product_update = 1
        if (isset($input['product_update']) && $input['product_quantity'] > 0 ) {
            //l???y danh s??ch topping
            $listTopping = OrderProductTopping::where('product_id', $productId)
                ->where('order_product_id', $orderProductId)
                ->where('order_id', $orderId)
                ->pluck('topping_id');
            $input['topping'] = implode(',', $listTopping->toArray());
            //l???y danh s??ch option
            $listOptions = OrderProductOption::where('product_id', $productId)
                ->where('order_product_id', $orderProductId)
                ->where('order_id', $orderId)
                ->pluck('option_id');
            $input['option'] = implode(',', $listOptions->toArray());
            //l???y th??ng tin size
            $input['size_id'] = $orderProduct->size_id;
            //l???y th??ng tin product(comment)
            $input['product_comment'] = $orderProduct->order_product_comment;
            $this->commonDeleteProduct($orderProductId);
            $this->customerAddProduct($input, $orderId);
            return $this->cartListProduct($input);
        }

        $this->commonDeleteProduct($orderProductId);
        if ($input['product_quantity'] == 0) {
            return $res;
        }
        if (isset($input['product_delete'])) {
            if ($input['product_delete'] == ProductDataConst::PRODUCT_REMOVE_CART) {
                return $res;
            }
        }
        //t???o m???i c??ng v???i orderId
        $this->customerAddProduct($input, $orderId);
        return $this->cartListProduct($input);
    }

    public function checkUsingAtProduct($product, $usingAt)
    {
        if ($product->using_at != $usingAt && $product->using_at != ProductDataConst::PRODUCT_USING_AT_ALL) {
            return false;
        }
        return true;
    }

    public function cartChangeUsingAt($input)
    {
        $customerToken = $input['customer_token'];
        $customerId = $input['customer_id'];
        $checkToken = $this->checkCustomerToken($customerToken);
        if (!$checkToken || !isset($input['customer_id'])) {
            return false;
        }
        $res = [];
        $listProductId = explode(',', $input['list_product_id']);
        $usingAt = $input['using_at'];
        $products = Product::whereIn('id', $listProductId)->get();
        $productIdUsingAt = [];
        $arrayUsingAt = [$usingAt, ProductDataConst::PRODUCT_USING_AT_ALL];
        foreach ($products as $key => $product) {
            if (in_array($product->using_at, $arrayUsingAt)) {
                $productIdUsingAt[] = $product->id;
            }
            $res[$key]['product_id'] = $product->id;
            $res[$key]['product_can_change'] = $this->checkUsingAtProduct($product, $usingAt);
        }
        $order = Order::where('customer_id', $customerId)->where('status', OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED)->first();
        if (!$order) {
            return 'customer_id = ' . $customerId . 'khong co order';
        }
        //danh s??ch s???n ph???m c?? using_at = $usingAt

        $total_product_price = OrderProduct::where('order_id', $order->id)
            ->where('status', OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED)
            ->whereIn('product_id', $productIdUsingAt)
            ->sum('total_price');
        $result['total_product_price'] = $total_product_price;
        $result['list_product'] = $res;
        return $result;
    }

    public function cartCancelProduct($input)
    {
        $orderProductId = $input['order_product_id'];
        $orderProduct = OrderProduct::find($orderProductId);
        $orderId = $orderProduct->order_id;
        if ($input['cancel_product'] == ProductDataConst::PRODUCT_CANCEL_BY_CUSTOMER_TRUE) {
            $orderProduct->update(['status' => OrderDataConst::ORDER_STATUS_CUSTOMER_CANCEL]);
        }
        if ($input['cancel_product'] == ProductDataConst::PRODUCT_CANCEL_BY_CUSTOMER_FALSE) {
            $orderProduct->update(['status' => OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED]);
        }
        $this->updateOrderCommon($orderId);
        return $this->cartListProduct($input);
    }

    public function cartFinish($input)
    {
        $check = $this->checkCustomerLogin($input);
        if (!$check) {
            return false;
        }
        //customer_id, customer_token, voucher_code, customer_friend_id, address, location_lat, location_long, customer_option_chosse_id, using_at, order_comment
        $customerId = $input['customer_id'];
        $res = [];
        //update order
        $order = Order::where('customer_id', $customerId)->where('status', OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED)->first();
        if (!$order) {
            return 'sai order';
        }
        $orderId = $order->id;
        $voucherCode = null;
        if (isset($input['voucher_code'])) {
            $voucherCode = $input['voucher_code'];
        }

        $data = $this->updateOrderCommon($orderId, $voucherCode);
        if (!$data) {
            return 'sai update order';
        }
        //update order
        $amount = $order->total_product_price - $order->total_topping_price;
        $customer_phone = $customer_name = '';
        $customer = Customer::find($customerId);
        if ($customer) {
            $customer_phone = $customer->phone;
            $customer_name = $customer->name;
        }
        $orderCode = $order->id . generateRandomString(32);
        $orderUpdate['code'] = $orderCode;
        $orderUpdate['customer_id'] = $customerId;
        $orderUpdate['amount'] = $amount;
        $orderUpdate['customer_phone'] = $customer_phone;
        $orderUpdate['customer_name'] = $customer_name;
        $orderUpdate['table_id'] = $this->getValueDefault($input, 'table_id');
        $orderUpdate['level_id'] = $this->getValueDefault($input, 'level_id');
        $orderUpdate['ship_id'] = $this->getValueDefault($input, 'ship_id');
        $orderUpdate['ship_price'] = $this->getValueDefault($input, 'ship_price');
        $orderUpdate['order_type_id'] = OrderDataConst::ORDER_TYPE_CUSTOMER_APP;
        $orderUpdate['comment'] = $this->getValueDefault($input, 'order_comment');
        $orderUpdate['status'] = OrderDataConst::ORDER_STATUS_CUSTOMER_FINISH;
        $orderUpdate['order_use'] = $this->getValueDefault($input, 'order_use');
        $order->update($orderUpdate);
        //t???o m???i record trong b???ng customer_address
        $customer_address['location_lat'] = $this->getValueDefault($input, 'location_lat');
        $customer_address['location_long'] = $this->getValueDefault($input, 'location_long');
        $customer_address['address'] = $this->getValueDefault($input, 'address');
        $customer_address['favorite'] = $this->getValueDefault($input, 'favorite');
        $customer_address['voucher_code'] = $this->getValueDefault($input, 'voucher_code');
        $customer_address['customer_id'] = $this->getValueDefault($input, 'customer_id');
        $customer_address['customer_friend_id'] = $this->getValueDefault($input, 'customer_friend_id');
        $customer_address['using_at'] = $this->getValueDefault($input, 'using_at');
        $customer_address['customer_option_chosse_id'] = $this->getValueDefault($input, 'customer_option_chosse_id');
        CustomerAddress::create($customer_address);
        //update order_product where status = OrderDataConst::ORDER_STATUS_CUSTOMER_CREATED to OrderDataConst::ORDER_STATUS_CUSTOMER_FINISH;
        OrderProduct::where('order_id', $orderId)->update(['status' => OrderDataConst::ORDER_STATUS_CUSTOMER_FINISH]);
        $res['order_code'] = $orderCode = $customerId . generateRandomString(10);
        return $res;
    }
}
