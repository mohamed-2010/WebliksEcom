<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\Role;
use App\Models\RoleTranslation;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:view_staff_roles'])->only('index');
        $this->middleware(['permission:add_staff_role'])->only('create');
        $this->middleware(['permission:edit_staff_role'])->only('edit');
        $this->middleware(['permission:delete_staff_role'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::where('id','!=',1)->paginate(10);
        return view('backend.staff.staff_roles.index', compact('roles'));

        // $roles = Role::paginate(10);
        // return view('backend.staff.staff_roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.staff.staff_roles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->permissions);
        $role = Role::create(['name' => $request->name]);
        $role->givePermissionTo($request->permissions);

        $role_translation = RoleTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'role_id' => $role->id]);
        $role_translation->name = $request->name;
        $role_translation->save();

        flash(translate('New Role has been added successfully'))->success();
        return redirect()->route('roles.index');
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
    public function edit(Request $request, $id)
    {
        // $permission = Permission::create(['guard_name' => 'web', 'name' => 'add_new_branch', 'section' => 'branch']);
        $lang = $request->lang;
        $role = Role::findOrFail($id);
        //$permission = Permission::create(['guard_name' => 'web', 'name' => 'update_order_shipping_company', 'section' => 'sale']);
        //$permission = Permission::create(['guard_name' => 'web', 'name' => 'add_new_branch', 'section' => 'branch']);
        //$permission = Permission::findByName('update_order_shipping_company', 'web');
        //remove permission
        //$role->revokePermissionTo($permission);
        //return dd($permission);
        // create 5 permissions (add_order_item, create_discount, edit_discount, delete_discount, view_discount, update_discount)
        // $add_order_item = Permission::create(['guard_name' => 'web', 'name' => 'add_order_item', 'section' => 'sale']);
        // $create_discount = Permission::create(['guard_name' => 'web', 'name' => 'create_discount', 'section' => 'marketing']);
        // $edit_discount = Permission::create(['guard_name' => 'web', 'name' => 'edit_discount', 'section' => 'marketing']);
        // $delete_discount = Permission::create(['guard_name' => 'web', 'name' => 'delete_discount', 'section' => 'marketing']);
        // $view_discount = Permission::create(['guard_name' => 'web', 'name' => 'view_discount', 'section' => 'marketing']);
        // $update_discount = Permission::create(['guard_name' => 'web', 'name' => 'update_discount', 'section' => 'marketing']);
        // // assign permissions to role
        // $role->givePermissionTo($add_order_item);
        // $role->givePermissionTo($create_discount);
        // $role->givePermissionTo($edit_discount);
        // $role->givePermissionTo($delete_discount);
        // $role->givePermissionTo($view_discount);
        // $role->givePermissionTo($update_discount);

        return view('backend.staff.staff_roles.edit', compact('role','lang'));
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
        $role = Role::findOrFail($id);
        if($request->lang == env("DEFAULT_LANGUAGE")){
            $role->name = $request->name;
        }
        $role->syncPermissions($request->permissions);
        $role->save();

        // Role Translation
        $role_translation = RoleTranslation::firstOrNew(['lang' => $request->lang, 'role_id' => $role->id]);
        $role_translation->name = $request->name;
        $role_translation->save();

        flash(translate('Role has been updated successfully'))->success();
        return back();
        // return redirect()->route('roles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        RoleTranslation::where('role_id',$id)->delete();
        Role::destroy($id);
        flash(translate('Role has been deleted successfully'))->success();
        return redirect()->route('roles.index');
    }

    public function add_permission(Request $request)
    {
        $permission = Permission::create(['name' => $request->name, 'section'=> $request->parent]);
        return redirect()->route('roles.index');
    }

    public function create_admin_permissions(){
        
    }
}
