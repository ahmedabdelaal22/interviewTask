<?php

namespace App\Service;

use Illuminate\Http\Request;
use Modules\Acl\Repositories\UserRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * @method getAccount()
 * @method getSheetDrive($account, $id)
 */
class UserService 
{
    protected RoleService $roleService;
    protected  $repo;

    /**
     * Create a new Repository instance.
     *
     * @return void
     */
    public function __construct(UserRepository $repository, RoleService $roleService)
    {
        $this->repo = $repository;
        $this->roleService = $roleService;
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

    public function roleList()
    {
        $data = [];
        if (user()->can('update_users')) {
            $data = $this->roleService->list(new Request(['active' => activeType()['as']]));
        }
        return $data;
    }



    public function sendLink(Request $request){
        $status = Password::sendResetLink(
            $request->only('email')
        );
        return $status === Password::RESET_LINK_SENT
            ? response()->json(['status' => __($status)])
            : response()->json(['email' => __($status)]);
    }
        //toDO maged change it
    public function ResetPassword(Request $request){
        $updatePassword = DB::table('password_resets')
        ->where([
            'email' => $request->email,
            'token' => $request->token
        ])
        ->first();

    $user = User::where('email', $request->email)->first();
    $status = Password::tokenExists($user, $request->token);

    if (!$status) {

        return false;

    }

    User::where('email', $request->email)->update(['password' => Hash::make($request->password)]);

    DB::table('password_resets')->where(['email' => $request->email])->delete();

    return true;
    }

}
