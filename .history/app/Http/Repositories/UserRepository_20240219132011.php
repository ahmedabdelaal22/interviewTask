<?php

namespace Modules\Acl\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserRepository 
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id', 'first_name','last_name' ,'email', 'photo'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return User::class;
    }


    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

 
    /**
     * This function finds data based on given parameters and returns it with optional pagination and
     * related data.
     * 
     * @param Request request  is an instance of the Request class in Laravel, which represents
     * an HTTP request. It contains information about the request such as the HTTP method, URL,
     * headers, and any data sent in the request body.
     * @param withRelations An array of relationships to eager load with the query. This can help
     * reduce the number of database queries needed to retrieve related data.
     * @param moreConditionForFirstLevel `moreConditionForFirstLevel` is an optional parameter that
     * allows the caller to add additional conditions to the query for the first level of the model.
     * This can be useful when you want to filter the results based on some criteria that are not
     * included in the request parameters. For example, you might want
     * @param pagination A boolean value that determines whether or not to paginate the results. If set
     * to true, the results will be paginated based on the perPage parameter. If set to false, all
     * results will be returned.
     * @param perPage The number of records to be displayed per page when pagination is enabled.
     * 
     * @return The `findBy` function is returning the result of calling the `all` function with the
     * parameters passed to it.
     */
    public function findBy(Request $request, $withRelations = [], $moreConditionForFirstLevel = [], $pagination = false, $perPage = 10)
    {
        return [];
        //$this->all($request->all(), withRelations: $withRelations, moreConditionForFirstLevel: $moreConditionForFirstLevel, pagination: $pagination, perPage: $perPage);
    }

    /**
     * This function finds and returns a single record from a database table based on its ID.
     * 
     * @param id  is a parameter that represents the unique identifier of the record that you want
     * to retrieve from the database. It is used to query the database and fetch a single record that
     * matches the specified id.
     * 
     * @return The `findOne` function is returning the result of calling the `find` function with the
     * `` parameter and an array containing a single element `'*'`. The `find` function is likely
     * returning a single record from a database table based on the `` parameter and the array of
     * columns to select. The specific implementation of the `find` function is not shown in the code
     * snippet provided
     */
    public function findOne($id)
    {
        return $this->model()->find($id, ['*']);
    }

    /**
     * This function saves user data to the database and updates the password if provided, while also
     * syncing the user's roles if specified.
     * 
     * @param Request request An instance of the Request class, which contains the data submitted in
     * the HTTP request.
     * @param id  is a parameter that represents the ID of the user being updated. If  is not
     * null, it means that the function is updating an existing user, otherwise, it means that the
     * function is creating a new user.
     * 
     * @return either the updated data (if  is set) or the newly created data (if  is not set),
     * after syncing the roles if the role_id is set in the request. The function is also wrapping the
     * database operations in a transaction.
     */
    public function save(Request $request, $id = null)
    {
        return DB::transaction(function () use ($request, $id) {
            if (isset($request->password)) {
                $request->merge(['password' => Hash::make($request->password)]);
            }

            if ($id) {
                $data = $this->update($request->all(), $id);
            } else {
                $data = $this->create($request->all());
            }

            if (isset($request->role_id)) {
                $data->roles()->sync($request->role_id);
            }

            return isset($id) ? $this->findOne($id) : $data;
        });
    }
        /**
     * This function creates a new record in the database using the provided request data.
     *
     * @param request  is a variable that contains the data sent in the HTTP request. It could
     * be an array or an object that contains the values of the fields submitted in a form or in the
     * body of a request. This function is likely part of a controller in a web application that
     * handles the creation of a new
     *
     * @return The `create` method is returning the result of calling the `create` method on the model
     * property with the `` parameter passed in. The specific return value depends on the
     * implementation of the `create` method in the model, but it is likely to be a new instance of the
     * model with the attributes set according to the `` data.
     */
    public function create($request)
    {
        return $this->model->create($request);
    }

    /**
     * This function updates a record in the database and returns the updated record.
     *
     * @param request  is a variable that contains the data that is being sent in the HTTP
     * request. It could be data from a form submission or an API call. In this context, it is being
     * used to update the data in the database.
     * @param id The  parameter is an optional parameter that represents the unique identifier of
     * the data that needs to be updated. If it is not provided, the function will not be able to find
     * the data to update.
     *
     * @return The updated data with the specified ID is being returned.
     */
    public function update($request, $id = null)
    {
        $data = $this->findOne($id);
        $data->update($request);

        return $this->findOne($id);
    }



}
