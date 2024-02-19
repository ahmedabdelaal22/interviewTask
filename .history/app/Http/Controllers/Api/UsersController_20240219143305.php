<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\User\EditRequest;
use App\Http\Requests\User\CreateRequest;
use App\Traits\GeneralTrait;
use App\Http\Resources\UserResource;
use App\Service\UserService;
class UsersController extends Controller
{
    use GeneralTrait;
    private $service;

    public function __construct(UserService $Service)
    {
        $this->service = $Service;
    }
        /**
     * It takes a request, merges the request with the user's target market, and then returns the
     * response from the service
     *
     * @param Request request The request object
     *
     * @return The list of all the users in the database.
     */
    public function list(Request $request)
    {
        return $this->apiResponse($this->service->list($request, $this->pagination(), $this->perPage()));
    }

    /**
     * It takes the id of a product and returns the product with the target market of the user
     *
     * @param id The id of the record you want to show
     *
     * @return The show method is returning the result of the service show method.
     */
    public function show($id)
    {
        $data = $this->service->show($id);

        if ($data) {
            return $this->apiResponse(new UserResource($data));
        }

        return $this->notFoundResponse(trans('users.notFound'));
    }

    /**
     * It checks if the product exists in the favorites table, if it does, it returns an error message,
     * if it doesn't, it adds the product to the favorites table
     *
     * @param CreateRequest request
     */
    public function store(CreateRequest $request)
    {
        try {
            $user = $this->service->store($request);

            if ($user) {
                return $this->apiResponse($user,' A new user has added successfully');
            }

            return $this->unKnowError();
        } catch (\Exception $Exception) {
            return $this->unKnowError($Exception->getMessage());
        }
    }

    /**
     * It updates the user and returns the updated user if it's updated successfully, otherwise it
     * returns an unknown error.
     *
     * @param Request request The request object.
     * @param id The id of the user you want to update.
     */
    public function update(EditRequest $request, $id)
    {
        if (!$this->service->show($id)) {
            return $this->notFoundResponse(trans('users.notFound'));
        }

        try {
            $user = $this->service->update($request, $id);
            if ($user) {
                return $this->updateResponse($user, trans('users.A updated user has successfully'));
            }

            return $this->unKnowError();
        } catch (\Exception $Exception) {
            return $this->unKnowError($Exception->getMessage());
        }
    }
       /**
     * Delete Dropshipper
     * 
     * The Delete Dropshipper endpoint allows users to delete their dropshipper account from the system. 
     * This endpoint provides a way for users to permanently remove their account and associated data.
     *
     * This endpoint deletes the dropshipper account based on the provided request parameters. 
     * The user needs to provide the necessary details or confirmation to initiate the account deletion process.
     * 
     * @authenticated
     */
    public function delete(Request $request)
    {
        $data = $this->service->delete($request);
        if ($data) {
            return $this->deleteResponse(trans('auth.Done Delete User'));
        }
        return $this->unKnowError(trans('auth.failed'));
    }

}
