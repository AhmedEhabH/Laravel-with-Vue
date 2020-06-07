<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        // $this->authorize("isAdmin");
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        // return User::all();
        // return User::latest()->paginate(10);
        // $this->authorize("isAdmin");
        if(\Gate::allows('isAdmin') || \Gate::allows('isAuthor')){
            return User::latest()->paginate(10);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $this->authorize("isAdmin");

        $this->validate($request, [
            'name'      =>  'required|string|max:191',
            'email'     =>  'required|string|email|max:191|unique:users',
            'password'  =>  'required|string|min:6',
        ]);

        return User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'bio' => $request['bio'],
            'type' => $request['type'],
            'photo' => $request['photo'],
            'password' => Hash::make($request['password']),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $this->authorize("isAdmin");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $this->authorize("isAdmin");

        $user = User::findOrFail($id);

        $this->validate($request, [
            'name'      =>  'required|string|max:191',
            'email'     =>  'required|string|email|max:191|unique:users,email,'.$user->id,
            'password'  =>  'sometimes|min:6',
        ]);

        $user->update($request->all());

        return [
            "message" => "Updateing...",
            "ID" => $id,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $this->authorize("isAdmin");

        $user = User::findOrFail($id);

        $user->delete();
        
        return [
            'message' => "User Deleted"
        ];
    }

    /**
     * Profile methods
     */

    public function profile()
    {
        //
        return auth('api')->user();
        
    }
    public function updateProfile(Request $request)
    {
        //
        $user = auth('api')->user();

        $this->validate($request, [
            'name'      =>  'required|string|max:191',
            'email'     =>  'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|string|min:8',
        ]);
        
        $currentPhoto = $user->photo;
        if($request->photo != $currentPhoto){
            $type = explode('/', substr($request->photo, 0, strpos($request->photo, ';')))[1];
            $name = date('Y-m-d-H-i-s', time()) . "-{$user->id}.{$type}";
            Image::make($request->photo)->save(public_path('img/profile/').$name);
            // $user->photo = $name;
            $request->merge(['photo' => $name]);
            
            $userPhoto = public_path('img/profile/').$currentPhoto;
            if($currentPhoto != "profile.png" && file_exists($userPhoto)){
                @unlink($userPhoto);
            }
        }
        if(!empty($request->password)){
            $request->merge(['password' => Hash::make($request['password'])]);
        }

        $user->update($request->all());
        return [
            'message'   =>"SUCCESS",
        ];
    }
}
