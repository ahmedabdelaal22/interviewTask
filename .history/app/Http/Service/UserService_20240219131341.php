<?php

namespace App\Service;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;
/**
 * @method getAccount()
 * @method getSheetDrive($account, $id)
 */
class UserService 
{
    protected  $repo;

    /**
     * Create a new Repository instance.
     *
     * @return void
     */
    public function __construct(UserRepository $repository)
    {
        $this->repo = $repository;
   
    }

    public function findBy(Request $request, $withRelations = [], $moreConditionForFirstLevel = [], $pagination = false, $perPage = 10)
    {
        return $this->repo->findBy($request, $withRelations, $moreConditionForFirstLevel, $pagination, $perPage);
    }

    public function store(Request $request)
    {
        $request->merge(['status' => 1]);

        $data = $this->repo->save($request);
        if ($data) {
            return true;
        }
        return false;
    }

    public function update(Request $request, $id)
    {
        if (!user()->can('update_users')){
            $request->request->remove('role_id');
        }
        $data = $this->repo->save($request, $id);
        if ($data) {
            return $data;
        }
        return false;
    }
}
