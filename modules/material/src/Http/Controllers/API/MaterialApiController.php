<?php
namespace APV\Material\Http\Controllers\API;

use APV\Base\Http\Controllers\API\ApiBaseController;
use APV\Material\Services\MaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use APV\Material\Constants\MaterialResponseCode;
use APV\Base\Services\ApiAuth;
/**
 * Class MaterialApiController
 * @package APV\Material\Http\Controllers\API
 */
class MaterialApiController extends ApiBaseController
{
    public function __construct(MaterialService $materialService, ApiAuth $apiAuth)
    {
        $this->materialService = $materialService;
        $this->apiAuth = $apiAuth;
    }

    public function getList()
    {
        $data = $this->materialService->getList();
        return $this->sendSuccess($data, 'success');
    }

    public function postCreate(Request $request)
    {
        $input = $request->all();
        if (!$this->apiAuth->checkPermissionModule('material', 'postCreate')) {
            return $this->sendError(MaterialResponseCode::ERROR_CODE_NO_PERMISSION);
        }
        $data = $this->materialService->postCreate($input);
        if (!$data) {
            return $this->sendError(MaterialResponseCode::ERROR_CODE_UNCREATE_NEW);
        }
        return $this->sendSuccess($data, 'Create success');
    }

    public function getDetail($materialId)
    {
        $data = $this->materialService->getDetail($materialId);
        if (!$data) {
            return $this->sendError(MaterialResponseCode::ERROR_CODE_DETAIL);
        }
        return $this->sendSuccess($data, 'Detail success');
    }

    public function postEdit(Request $request, $materialId)
    {
        $input = $request->all();
        if (!$this->apiAuth->checkPermissionModule('material', 'postEdit')) {
            return $this->sendError(MaterialResponseCode::ERROR_CODE_NO_PERMISSION);
        }
        $data = $this->materialService->postEdit($materialId, $input);
        if (!$data) {
            return $this->sendError(MaterialResponseCode::ERROR_CODE_EDIT);
        }
        return $this->sendSuccess($data, 'Edit success');
    }

    public function postDelete(Request $request, $materialId)
    {
        $input = $request->all();
        if (!$this->apiAuth->checkPermissionModule('material', 'postDelete')) {
            return $this->sendError(MaterialResponseCode::ERROR_CODE_NO_PERMISSION);
        }
        $data = $this->materialService->postDelete($materialId);
        if (!$data) {
            return $this->sendError(MaterialResponseCode::ERROR_CODE_DELETE);
        }
        return $this->sendSuccess($data, 'Delete success');
    }
}
