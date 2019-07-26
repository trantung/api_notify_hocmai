<?php

namespace APV\Base\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseService
 * @package APV\Base\Services
 */
class BaseService
{
    protected $model;

    /**
     * BaseService constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->model->getTable();
    }
}
