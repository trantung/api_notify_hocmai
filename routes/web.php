<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//use Illuminate\Routing\Route;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use APV\Product\Models\GroupOption;
use APV\Product\Models\GroupOptionProduct;
use APV\Product\Models\Option;
use APV\Product\Models\OptionProduct;
use APV\Product\Models\Product;
use APV\Customer\Models\Customer;
use APV\Customer\Models\CustomerFriend;
use APV\Promotion\Models\Voucher;
use APV\Size\Models\SizeProduct;
use APV\Topping\Models\Topping;
use APV\Product\Models\ProductTopping;

Route::get('/firebase','FireBaseController@index');
Route::get('/testFire', function () {
    return view('test');
});

Route::get('/default_group_option', function(){
    $data = ['Độ ngọt', 'Độ chua'];
    foreach ($data as $key => $value) {
        GroupOption::create(['name' => $value]);
    }
    dd('group option');
});
Route::get('/default_option', function(){
    $data1 = ['Nhiều đường', 'ít đường', 'Không có đường'];
    $data2 = ['Chua nhiều', 'chua ít', 'Không chua'];
    foreach ($data1 as $key => $value) {
        Option::create([
            'group_option_id' => 1,
            'name' => $value,
            'status' => 1,
        ]);
    }
    foreach ($data2 as $key1 => $value1) {
        Option::create([
            'group_option_id' => 2,
            'name' => $value1,
            'status' => 1,
        ]);
    }
    dd('option');
});
Route::get('/default_product_group_option', function(){
    $data = Product::all();
    $group = GroupOption::all();
    foreach ($data as $product) {
        foreach ($group as $value) {
            $type = getRandomType();
            if ($type == 2) {
                $typeShow = GROUP_OPTION_PRODUCT_TYPE_SHOW_CHECKBOX;
            } else {
                $typeShow = getRandomTypeShow();
            }
            GroupOptionProduct::create([
                'product_id' => $product->id,
                'group_option_id' => $value->id,
                'type' => $type,
                'type_show' => $typeShow,
            ]);
        }
    }
    dd('default_product_group_option');
});
Route::get('/default_product_option', function(){
    $data = Product::all();
    $options = Option::all();
    foreach ($data as $product) {
        foreach ($options as $value) {
            OptionProduct::create([
                'product_id' => $product->id,
                'option_id' => $value->id,
            ]);
        }
    }
    dd('default_product_option');
});
Route::get('/default_customer_friend', function(){
    $data = Customer::all();
    $arrayName = array(
        'cavoisatthu', 'gau me vi dai', 'lon to da man'
    );
    $arrayPhone = array(
        '0912957368', '0943174218', '0912190812'
    );
    $arrayId = array(1,2,3);
    foreach ($data as $value) {
        foreach ($arrayName as $k => $v) {
            CustomerFriend::create([
                'customer_id' => $value->id,
                'customer_phone' => $value->phone,
                'friend_id' => $arrayId[$k],
                'friend_name' => $arrayName[$k],
                'friend_phone' => $arrayPhone[$k],
                'avatar' => ''
            ]);
        }
    }
    dd('default_customer_friend');
});
Route::get('/default_product_des', function(){
    $data = Product::all();
    $text = 'Cafe cốt dừa là một loại cafe đang rất được ưu chuộng hiện nay tại các quán cafe bởi hương vị mới lạ và ngon miệng. Khi nước cốt dừa hòa quyện với cà phê sẽ giúp đẩy mùi vị của cà phê hấp dẫn hơn rất nhiều. Với những ai yêu cà phê và thích thú với hương vị béo ngậy của dừa hòa quyện với vị đắng đậm đà của cà phê thì không thể bỏ qua món đồ uống này.';
    foreach ($data as $key => $value) {
        if ($value->description == '') {
            $value->update(['description' => $text]);
        }
    }
    dd('default_product_des');
});
Route::get('/default_product_short_des', function(){
    $data = Product::all();
    $text = 'Giới thiệu ngắn sản phẩm';
    foreach ($data as $key => $value) {
        if ($value->short_desc == '') {
            $value->update(['short_desc' => $text . ' số ' . $value->id]);
        }
    }
    dd('default_product_short_des');
});

Route::get('/default_voucher', function(){
    $data = [
        'name' => 'voucher test hết hạn',
        'status' => 0,
        'start_time' => '2020-07-01 15:00:00',
        'end_time' => '2020-07-02 16:00:00',
        'money_promotion' => 0,
        'percent_promotion' => 10,
        'code' => 'MEGUU_EXPIRED',
        'quantity' => 1000,
    ];
    $id = Voucher::create($data)->id;
    dd($id);
});

Route::get('/product_size_active_default', function(){
    $products = Product::all();
    foreach ($products as $key => $value) {
        SizeProduct::create([
            'product_id' => $value->id,
            'size_id' => 1,
            'price' => 10000,
            'active' => 1,
            'weight_number' => 1
        ]);
        SizeProduct::create([
            'product_id' => $value->id,
            'size_id' => 2,
            'price' => 20000,
            'active' => 0,
            'weight_number' => 2
        ]);
        SizeProduct::create([
            'product_id' => $value->id,
            'size_id' => 3,
            'price' => 30000,
            'active' => 0,
            'weight_number' => 3
        ]);

    }
    dd('product_size_active_default');
});
//fake product_topping
Route::get('/product_topping_fake', function(){
    $products = Product::all();
    $toppings = Topping::all();
    foreach ($products as $product) {
        foreach ($toppings as $topping) {
            ProductTopping::create([
                'product_id' => $product->id,
                'topping_id' => $topping->id,
                'status' => 1,
                'source' => 0,
            ]);
        }
    }
    dd('product_topping_fake');
});
//random using_at for product
Route::get('/product_using_at_fake', function(){
    $products = Product::all();
    foreach ($products as $product) {
        $product->update(['using_at' => getRandomTypeShow()]);
    }
    dd('product_using_at_fake');
});

Route::get('/voucher_des', function(){
    $data = Voucher::all();
    $avatar = '/uploads/img/default.jpg';
    foreach ($data as $value) {
        $value->update([
            'description' => 'Giới thiệu voucher id số ' . $value->id,
            'avatar' => $avatar,
        ]);
    }
    dd('voucher_des');
});



Route::get('/admin/login', ['uses' => 'AdminController@getLogin', 'as' =>'login']);
Route::post('/admin/login', ['uses' => 'AdminController@postLogin']);
Route::post('/admin/logout', ['uses' => 'AdminController@postLogout', 'as' =>'logout']);

Route::group(['prefix' => '/admin', 'middleware' => 'auth:web'], function () {
    Route::get('/dashboard', 'AdminController@index');
    Route::get('/error', 'AdminController@getError');
    Route::get('/blank', 'AdminController@getBlank');
    Route::get('/tables', 'AdminController@getTables');
    Route::get('/charts', 'AdminController@getCharts');
    Route::get('/register', 'AdminController@getRegister');
    //Role
    Route::resource('/role', 'RoleController');
    //Tầng(level)
    Route::resource('/level', 'LevelController');
    //Category
    Route::resource('/category', 'CategoryController');
    //Table
    Route::resource('/table', 'TableController');
    //User
    Route::resource('/user', 'UserController');
    //Size
    Route::resource('/size', 'SizeController');
    
    //Material type: don vi tinh cua nguyen lieu(kg, g...)
    Route::resource('/material_type', 'MaterialTypeController');
    //Material
    Route::resource('/material', 'MaterialController');
    //Product
    Route::get('/uploadFile','CommonImagesController@index')->name('products.index');;
    Route::post('/uploadFile/file','CommonImagesController@store')->name('products.store');
    Route::resource('/products','ProductController');
    // product_size
    Route::get('/size_product/size/{size_id}','SizeProductController@size')->where(['size_id'=>'[0-9]+']);
    Route::resource('/size_product','SizeProductController');
    //Topping cho category
    Route::resource('/topping', 'ToppingController');
    //Topping cho product
    Route::get('/product_topping/{id}', 'ProductToppingController@list');
    Route::get('/product_topping/{id}/create', 'ProductToppingController@create');
    Route::post('/product_topping/{id}/create', 'ProductToppingController@store');
    Route::get('/product_topping/{id}/edit/{product_topping_id}', 'ProductToppingController@edit');
    Route::post('/product_topping/{id}/edit/{product_topping_id}', 'ProductToppingController@update');
    Route::post('/product_topping/{id}/destroy/{product_topping_id}', 'ProductToppingController@destroy');
    //config material cho size_product
    Route::get('/size_product/{size_product_id}/config_material', 'SizeProductMaterialController@list');
    Route::get('/size_product/{size_product_id}/config_material/create', 'SizeProductMaterialController@create');
    Route::post('/size_product/{size_product_id}/config_material/create', 'SizeProductMaterialController@store');
    Route::get('/size_product/{size_product_id}/config_material/{size_product_material_id}/edit', 'SizeProductMaterialController@edit');
    Route::post('/size_product/{size_product_id}/config_material/{size_product_material_id}/edit', 'SizeProductMaterialController@update');
    Route::post('/size_product/{size_product_id}/config_material/{size_product_material_id}/destroy', 'SizeProductMaterialController@destroy');

});

