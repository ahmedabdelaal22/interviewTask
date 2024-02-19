<?php

namespace Modules\Acl\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Modules\Basic\Repositories\BasicRepository;

class UserRepository extends BasicRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id', 'name', 'email', 'status'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return User::class;
    }

    /**
     * The function returns an empty array for a translation key in PHP.
     * 
     * @return An empty array is being returned.
     */
    public function translationKey()
    {
        return [];
    }

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * This function returns the searchable relationship fields of a model.
     * 
     * @return the value of the property `searchRelationShip` of the object `->model`.
     */
    public function getFieldsRelationShipSearchable()
    {
        return $this->model->searchRelationShip;
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
        return $this->all($request->all(), withRelations: $withRelations, moreConditionForFirstLevel: $moreConditionForFirstLevel, pagination: $pagination, perPage: $perPage);
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
        return $this->find($id, ['*']);
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
     * This function updates the "suspended" field to true for users with a specific role and null
     * values in certain fields.
     * 
     * @param Request request  is an instance of the Request class which contains the data sent
     * by the client in the HTTP request. It is used to retrieve input data, such as form data or query
     * parameters, from the request. In this case, it is used to retrieve the role_id parameter from
     * the request.
     * 
     * @return a boolean value of true.
     */
    public function updateSuspended(Request $request)
    {

        $users = $this->model()->whereHas('roles', function ($q) use ($request) {
            $q->where('id', $request->role_id);
        });
        $users->where(function ($query) {
            foreach ($this->model()->$external_users_required_data as $index => $item) {
                if ($index == 0) $query->whereNull($item);
                $query->orWhereNull($item);
            }
        });
        $users->update(['suspended' => true]);
        return true;
    }
}
