<?php


namespace App\Repository\User\Role;


use App\Models\Role;
use App\Repository\Eloquent\BaseRepository;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Role $model
     */
    public function __construct(Role $model)
    {
        parent::__construct($model);
        $this->model = $model;
    }

    public function userRoleByCompanyAndSlug($company_id, $slug)
    {
        return $this->model->where('company_id', $company_id)->where('slug', $slug)->get();
    }

}
