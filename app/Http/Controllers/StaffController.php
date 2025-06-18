<?php

namespace App\Http\Controllers;

use App\Models\Branche;
use App\Models\BrancheUser;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Role;
use App\Models\User;
use Hash;
use Mpdf\Tag\Br;

class StaffController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:view_all_staffs'])->only('index');
        $this->middleware(['permission:add_staff'])->only('create');
        $this->middleware(['permission:edit_staff'])->only('edit');
        $this->middleware(['permission:delete_staff'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $staffs = Staff::paginate(10);
        return view('backend.staff.staffs.index', compact('staffs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::where('id','!=',1)->orderBy('id', 'desc')->get();
        $branches = Branche::get();
        return view('backend.staff.staffs.create', compact('roles', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
            ],
        ], [
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'password.regex' => 'كلمة المرور يجب أن تحتوي على حرف كبير (Uppercase)، حرف صغير (Lowercase)، ورقم (Number).',
        ]);

        if(User::where('email', $request->email)->first() == null){
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->mobile;
            $user->user_type = "staff";
            $user->password = Hash::make($request->password);
            if($user->save()){
                $staff = new Staff;
                $staff->user_id = $user->id;
                $staff->role_id = $request->role_id;
                $user->assignRole(Role::findOrFail($request->role_id)->name);
                if($staff->save()){
                    flash(translate('Staff has been inserted successfully'))->success();
                    //branches assign
                    if($request->branches != null){
                        foreach ($request->branches as $key => $branch) {
                            BrancheUser::create([
                                'user_id' => $user->id,
                                'branche_id' => $branch
                            ]);
                        }
                    }
                    return redirect()->route('staffs.index');
                }
            }
        }

        flash(translate('Email already used'))->error();
        return back();
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $staff = Staff::findOrFail(decrypt($id));
        $roles = $roles = Role::where('id','!=',1)->orderBy('id', 'desc')->get();
        $branches = Branche::get();
        $branch_user = BrancheUser::where('user_id', $staff->user_id)->get();
        return view('backend.staff.staffs.edit', compact('staff', 'roles', 'branches', 'branch_user'));
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
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
            ],
        ], [
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'password.regex' => 'كلمة المرور يجب أن تحتوي على حرف كبير (Uppercase)، حرف صغير (Lowercase)، ورقم ',
        ]);

        $staff = Staff::findOrFail($id);
        $user = $staff->user;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->mobile;
        if(strlen($request->password) > 0){
            $user->password = Hash::make($request->password);
        }
        if($user->save()){
            $staff->role_id = $request->role_id;
            if($staff->save()){
                $user->syncRoles(Role::findOrFail($request->role_id)->name);
                flash(translate('Staff has been updated successfully'))->success();
                //branches assign
                if($request->branches != null){
                    BrancheUser::where('user_id', $user->id)->delete();
                    foreach ($request->branches as $key => $branch) {
                        BrancheUser::create([
                            'user_id' => $user->id,
                            'branche_id' => $branch
                        ]);
                    }
                }
                return redirect()->route('staffs.index');
            }
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::destroy(Staff::findOrFail($id)->user->id);
        if(Staff::destroy($id)){
            flash(translate('Staff has been deleted successfully'))->success();
            return redirect()->route('staffs.index');
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }
}
