<?php

namespace App\Service;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Http\Resources\UserResource;
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
    public function show($id)
    {
        return new UserResource($this->repo->findOne($id));
    }

    public function store(Request $request)
    {
        $data = $this->repo->save($request);
        if ($data) {
            return true;
        }
        return false;
    }

    public function update(Request $request, $id)
    {
        $data = $this->repo->save($request, $id);
        if ($data) {
            return $data;
        }
        return false;
    }
      /**
     * It deletes the supplier's account
     *
     * @param Request request The request object
     *
     * @return The data is being returned.
     */
    public function delete($id = null)
    {
        $data = $this->repo->delete($id);
        if ($data) {
            return $data;
        }
        return false;
    }
}
