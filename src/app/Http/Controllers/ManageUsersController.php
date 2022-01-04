<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestRoleRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateCrewRequest;
use App\Http\Requests\UpdateUsersRequest;
use App\Models\Crew;
use App\Models\RoleRequest;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class ManageUsersController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //  $this->authorizeResource(User::class, 'user');
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->authorize("viewAny", Auth::user());

        $users = User::all();
        $request_roles = RoleRequest::where("granted", null)->get();
        return view("user.index", compact("users", "request_roles"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->authorize("create", Auth::user());
        return view('user.create');
    }


    /**
     * Create a newly registered user.
     *
     * @param array $input
     * @return User
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize("create", Auth::user());
        $user = $request->validated();
        DB::transaction(function () use ($user) {
            return tap(User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']),
                'role_id' => $user['role'],
                "crew_id" => Crew::getDefaultCrewId()
            ]), function (User $created_user) {
                $created_user->genToken();
                $created_user->genRole();
            });
        });

        return redirect()->route('user.index');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(String $id)
    {
        $this->authorize("view", Auth::user());
        $user = User::find($id);
        $roles = UserRole::all();
        return view("user.show", compact("user", "roles"));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @param $request
     * @param $user
     * @return Response
     */
    public function edit( $id)
    {
        $this->authorize("edit", Auth::user());
        $user_found = User::find($id);
        return view("user.edit", compact("user_found"));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param $user
     * @return Response
     */
    public function update(UpdateUsersRequest $request, $id)
    {
        // $id->update($request->validated());
        //$user->roles()->sync($request->input('roles', []));

        $this->authorize("edit", Auth::user());
        $user = $request->validated();

        User::find($id)
            ->update($user);

        return redirect()->route('user.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param $user
     * @return Response
     */
    public function destroy($id)
    {
        $this->authorize("delete", Auth::user());
        User::find($id)->delete();
        return redirect()->route("user.index");
    }


    /**
     * Add user role request to the request list
     *
     * @param StoreRequestRoleRequest $request
     * @param $id
     * @return RedirectResponse
     */
    public function RequestRole(StoreRequestRoleRequest $request, $id)
    {
        $role = $request->input('role');
        $user = User::find($id);
        if ($user->role->id == $role) {
            return redirect()->back();
        }
        RoleRequest::create(['user' => $user->id, 'role' => $role]);
        return redirect()->back();
    }

    /**
     * Grant the user role request
     *
     * @param $id
     * @return RedirectResponse
     */
    public function GrantRole($id)
    {
        $request = RoleRequest::find($id);
        $user = User::find($request->getUserId());
        $user->update(["role_id" => $request->getRoleId()]);
        $request->update(["granted" => date("Y-m-d H:i:s")]);

        return redirect()->back();
    }

    /**
     * Grant the user role request
     *
     * @param $id
     * @return RedirectResponse
     */
    public function RejectRole($id)
    {
        $request = RoleRequest::find($id);
        $request->update(["granted" => date("Y-m-d H:i:s")]);

        return redirect()->back();
    }

    /**
     * Add user role request to the request list
     *
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function ChangeTeam(UpdateCrewRequest $request, $id)
    {
        $crew = $request->input('crew');
        $user = User::find($id);
        $user->crew_id = $crew;
        $user->save();
        return redirect()->back();
    }
}
