<?php
namespace App\Repository\User;

use Ramsey\Collection\Collection;
use App\Repository\Eloquent\EloquentRepositoryInterface;


interface UserRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * @param $email
     * @return mixed
     */
    public function userFromEmail($email);

    /**
     * @param $id
     * @return mixed
     */
    public function userById($id);

    /**
     * @param $foreign_id
     * @param $company_id
     * @return mixed
     */
    public function getByForeignIdAndCompany_id($foreign_id, $company_id);

    /**
     * @param $username
     * @return mixed
     */
    public function getUserByUsername($username);

    /**
     * @param $username
     * @return mixed
     */
    public function firstUserByUsername($username);

    /**
     * @param $departments_id
     * @return mixed
     */
    public function getUserByDepartmentId(array $departments_id);

    /**
     * @return mixed
     */
    public function getUsersNoToken();

    /**
     * @return mixed
     */
    public function getUserWhereNotRegister();

    /**
     * @param $client_id
     * @return mixed
     */
    public function getByClientId($client_id);

    /**
     * @param $method
     * @param $column
     * @param $columnParam
     * @return mixed
     */
    public function allQuery($method, $column, $columnParam);



}
