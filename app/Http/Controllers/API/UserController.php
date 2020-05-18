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
        return User::latest()->paginate(10);
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

        if($request->photo){
            $type = explode('/', substr($request->photo, 0, strpos($request->photo, ';')))[1];
            $name = date('Y-m-d-H-i-s', time()) . "-{$user->id}.{$type}";
            Image::make($request->photo)->save(public_path('img/profile/').$name);
            // $user->photo = public_path('img/profile/').$name;
        }
        // $user->update($request->all());
        return [
            'message'   =>"SUCCESS",
            'Picture'   =>$request->photo,
            "type"      =>explode('/', substr($request->photo, 0, strpos($request->photo, ';')))[1], 
            "new-name"  =>$name,
            "time"      =>date('Y-m-d-H-i-s', time()),
        ];
    }
}
