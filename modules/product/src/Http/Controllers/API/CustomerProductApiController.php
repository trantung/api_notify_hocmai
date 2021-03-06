<?php
namespace APV\Product\Http\Controllers\API;

use APV\Base\Http\Controllers\API\ApiBaseController;
use APV\Product\Services\ProductService;
use Illuminate\Http\Request;
use APV\Base\Services\ApiAuth;
/**
 * Class CustomerProductApiController
 * @property ProductService $productService
 * @property ApiAuth $apiAuth
 */
class CustomerProductApiController extends ApiBaseController
{
     public function __construct(ProductService $productService, ApiAuth $apiAuth)
    {
        $this->productService = $productService;
        $this->apiAuth = $apiAuth;
    }

    public function getList(Request $request)
    {
        $input = $request->all();
        $data['data_detail'] = $this->productService->customerGetList($input);
        $data['meguu_fee'] = $this->getMeguuFee($input);
        $data['customer_action'] = $this->getCustomerAction();
        return $this->sendSuccess($data, 'success');
    }

    public function getDetail(Request $request)
    {
        $input = $request->all();
        $data['data_detail'] = $this->productService->customerGetDetail($input);
        $data['meguu_fee'] = $this->getMeguuFee($input);
        $data['customer_action'] = $this->getCustomerAction();
        return $this->sendSuccess($data, 'Detail success');
    }

    public function addProduct(Request $request)
    {
        $input = $request->all();
        $data['data_detail'] = $this->productService->customerAddProduct($input);
        $data['meguu_fee'] = $this->getMeguuFee($input);
        $data['customer_action'] = $this->getCustomerAction();
        return $this->sendSuccess($data, 'Detail success');
    }

    public function cartListProduct(Request $request)
    {
        $input = $request->all();
        $data['data_detail'] = $this->productService->cartListProduct($input);
        $data['meguu_fee'] = $this->getMeguuFee($input);
        $data['customer_action'] = $this->getCustomerAction();
        return $this->sendSuccess($data, 'Detail success');
    }

    public function cartEditProduct(Request $request)
    {
        $input = $request->all();
        $data['data_detail'] = $this->productService->cartEditProduct($input);
        $data['meguu_fee'] = $this->getMeguuFee($input);
        $data['customer_action'] = $this->getCustomerAction();
        return $this->sendSuccess($data, 'Detail success');
    }

    public function cartUpdateProduct(Request $request)
    {
        $input = $request->all();
        $data['data_detail'] = $this->productService->cartUpdateProduct($input);
        $data['meguu_fee'] = $this->getMeguuFee($input);
        $data['customer_action'] = $this->getCustomerAction();
        return $this->sendSuccess($data, 'Detail success');
    }

    public function cartChangeUsingAt(Request $request)
    {
        $input = $request->all();
        $data['data_detail'] = $this->productService->cartChangeUsingAt($input);
        $data['meguu_fee'] = $this->getMeguuFee($input);
        $data['customer_action'] = $this->getCustomerAction();
        return $this->sendSuccess($data, 'Detail success');
    }
    
    public function cartCancelProduct(Request $request)
    {
        $input = $request->all();
        $data['data_detail'] = $this->productService->cartCancelProduct($input);
        $data['meguu_fee'] = $this->getMeguuFee($input);
        $data['customer_action'] = $this->getCustomerAction();
        return $this->sendSuccess($data, 'Detail success');
    }

    public function cartFinish(Request $request)
    {
        $input = $request->all();
        $data['data_detail'] = $this->productService->cartFinish($input);
        $data['meguu_fee'] = $this->getMeguuFee($input);
        $data['customer_action'] = $this->getCustomerAction();
        return $this->sendSuccess($data, 'Detail success');
    }

}
