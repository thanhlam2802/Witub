<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Exceptions\ApiException;
use App\Exceptions\BusinessException;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    /**
     * Tiêm UserService vào Controller qua Dependency Injection.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        try {

            $search = $request->query('search');

            $users = $this->userService->getUsers(10, $search);

            return view('content.users.index', compact('users', 'search'));
        } catch (ApiException $e) {
            return back()->with('error', $e->getMessage());
        }
    }



    /**
     * Hiển thị form tạo người dùng mới.
     */
    public function create()
    {
        // Lấy danh sách role để hiển thị trong dropdown
        $roles = User::getRoleList();
        return view('content.users.create', compact('roles'));
    }

    /**
     * Lưu người dùng mới vào database.
     */
    public function store(StoreUserRequest $request)
    {
        try {

            $this->userService->createUser($request->validated());

            return redirect()->route('users.index')
                ->with('success', 'Tạo người dùng thành công!');
        } catch (BusinessException | ApiException $e) {

            return back()->with('error', $e->getMessage())->withInput();
        }
    }


    // public function edit(User $user)
    // {
    //     $roles = User::getRoleList();
    //     return view('content.users.edit', compact('user', 'roles'));
    // }


    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $this->userService->updateUser($user->user_id, $request->validated());

            return redirect()->route('users.index')
                ->with('success', 'Cập nhật người dùng thành công!');
        } catch (BusinessException | ApiException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }


    public function destroy(User $user)
    {
        try {
            $this->userService->deleteUser($user->user_id);

            return redirect()->route('users.index')
                ->with('success', 'Xóa người dùng thành công!');
        } catch (BusinessException | ApiException $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }
}
