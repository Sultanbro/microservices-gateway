<?php


namespace App\Repository\User;


use App\Models\User;
use App\Models\UserToken;
use App\Repository\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Mixed_;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
        $this->model = $model;
    }

    /**
     * @param $email
     * @return mixed
     */
    public function userFromEmail($email)
    {
        return $this->model->firstWhere('email', $email);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function userById($id)
    {
        return $this->model->firstWhere('id', $id);
    }

    /**
     * @param $foreign_id
     * @param $company_id
     * @return mixed
     */
    public function getByForeignIdAndCompany_id($foreign_id, $company_id)
    {
        return $this->model->whereForeign_idAndCompany_id($foreign_id, $company_id)->first();
    }

    /**
     * @param $username
     * @return mixed
     */
    public function getUserByUsername($username)
    {
        return $this->model->firstWhere('username', $username);
    }

    /**
     * @param $username
     * @return mixed
     */
    public function firstUserByUsername($username)
    {
        return $this->model->firstWhere('username', $username);
    }

    /**
     * @param array $departments_id
     * @return mixed
     */
    public function getUserByDepartmentId(array $departments_id)
    {
        return $this->model->whereIn('department_id', $departments_id)->get();
    }

    /**
     * @return mixed
     */
    public function getUsersNoToken()
    {
        return $this->model->doesntHave('token')->select('id')->get();
    }

    /**
     * @return mixed
     */
    public function getUserWhereNotRegister()
    {
       return $this->model->doesntHave('tokenNotRegister')->orderBy('id')->get();
    }

    /**
     * @return mixed
     */
    public function getUsersNotUsers()
    {
        return $this->model->doesntHave('notUserRole')->where('company_id', '<>',1)->orderBy('id')->get();
    }

    /**
     * @param $client_id
     * @return mixed
     */
    public function getByClientId($client_id)
    {
        return $this->model->where('client_id', $client_id)->get();
    }

    public function allQuery($method, $column, $columnParam)
    {
        if ($method === 'get')
        {
            return $this->model->where($column, $columnParam)->get();
        } elseif ($method === 'first')
        {
            return $this->model->firstWhere($column, $columnParam);
        }
    }
}
