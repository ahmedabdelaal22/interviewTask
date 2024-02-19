<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
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

}
