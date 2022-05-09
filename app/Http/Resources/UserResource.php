<?php

namespace App\Http\Resources;

use App\Http\Resources\Centcoin\CentcoinResource;
use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\Department\DepartmentResource;
use App\Http\Resources\User\CareerResource;
use App\Http\Resources\User\UserContactResource;
use App\Http\Resources\User\UserHistoryResource;
use App\Http\Resources\User\VacationResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $temp_details = '{
                            "duty": "Не заполнено",
                            "city": "Казахстан",
                            "married": "Не заполнено",
                            "edu": "Не заполнено",
                            "pro": "Не заполнено",
                            "datebeg": null,
                            "dateend": 0,
                            "countdays": 0,
                            "contactmail": "Не заполнено",
                            "workphone": "0",
                            "mobilephone": null,
                            "insidephone": null,
                            "AdmDays": 0,
                            "carier": null,
                            "vacation": null,
                            "admin": null
                            }';
        return [
            'id' => $this->id,
            'email' => $this->email,
            'company' => new CompanyResource($this->company),
//            'department' => new DepartmentResource($this->userDepartment),
            'user_info' => new ClientResource($this),
            'centcoin' => isset($this->centcoin[0]) ? $this->centcoin[0]->total : 0,
            'user_detail' => isset($this->details->user_info) ? json_decode($this->details->user_info) : json_decode($temp_details),
            'permission' => ['update' => $this->when(Gate::allows('update_avatar'), 'update_avatar'),
                            'crate' => $this->when(Gate::allows('create_avatar') or in_array($this->company_id, Auth::user()->roles()->where('slug', 'avatar')->pluck('company_id')->toArray()), 'create_avatar'),
                            'delete' => $this->when(Gate::allows('delete_avatar') or in_array($this->company_id, Auth::user()->roles()->where('slug', 'avatar')->pluck('company_id')->toArray()), 'delete_avatar'),],
        ];

    }

    public static function roleCompanyIdsBySlug($slug){
        if (Auth::check()) {
            return Auth::user()->roles()->where('slug', $slug)->pluck('company_id')->toArray();
        }
        return [];
    }
}
