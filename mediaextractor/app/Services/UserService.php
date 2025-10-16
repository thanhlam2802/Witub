<?php


namespace App\Services;

use App\Exceptions\ApiException;
use App\Exceptions\BusinessException;
use App\Exceptions\ValidationException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService extends BaseService
{
    protected UserRepository $userRepository;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Lấy danh sách người dùng có phân trang.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws ApiException
     */
    public function getUsers(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        try {
            return $this->userRepository->getPaginated($perPage, $search);
        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy danh sách người dùng: " . $e->getMessage());
            throw new ApiException("Hệ thống đang gặp sự cố, không thể lấy dữ liệu người dùng.");
        }
    }



    /**
     * Tìm một người dùng theo ID.
     *
     * @param int $id
     * @return User
     * @throws BusinessException
     */
    public function getUserById(int $id): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            // Lỗi nghiệp vụ: Yêu cầu một tài nguyên không tồn tại.
            throw new BusinessException("Không tìm thấy người dùng với ID này.");
        }

        return $user;
    }

    /**
     * Tạo người dùng mới.
     *
     * @param array $data
     * @return User
     * @throws ValidationException|BusinessException|ApiException
     */
    public function createUser(array $data): User
    {
        // 1. Validate dữ liệu đầu vào
        $validator = Validator::make($data, [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password_hash' => 'required|string|min:8',
            'role' => 'required|in:' . implode(',', [User::ROLE_ADMIN, User::ROLE_POSTER, User::ROLE_USER]),
        ]);

        if ($validator->fails()) {

            throw new ValidationException("Dữ liệu cung cấp không hợp lệ.", $validator->errors());
        }

        $validatedData = $validator->validated();


        $validatedData['full_name'] = $validatedData['full_name'] ?? explode('@', $validatedData['email'])[0];
        $validatedData['role'] = $validatedData['role'] ?? User::ROLE_USER;

        DB::beginTransaction();
        try {
            $user = $this->userRepository->create($validator->validated());
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi tạo người dùng mới: " . $e->getMessage());
            throw new ApiException("Đã có lỗi xảy ra trong quá trình tạo người dùng.");
        }
    }

    /**
     * Cập nhật thông tin người dùng.
     *
     * @param int $id
     * @param array $data
     * @return User
     * @throws ValidationException|BusinessException|ApiException
     */
    public function updateUser(int $id, array $data): User
    {
        // 1. Kiểm tra xem người dùng có tồn tại không
        $user = $this->getUserById($id);

        // 2. Validate dữ liệu
        $validator = Validator::make($data, [
            'full_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id . ',user_id',
            'password_hash' => 'nullable|string|min:8',
            'role' => 'sometimes|required|in:' . implode(',', [User::ROLE_ADMIN, User::ROLE_POSTER, User::ROLE_USER]),
        ]);

        if ($validator->fails()) {
            throw new ValidationException("Dữ liệu cập nhật không hợp lệ.", $validator->errors());
        }

        // 3. Thực hiện cập nhật
        DB::beginTransaction();
        try {
            $this->userRepository->update($id, $validator->validated());
            DB::commit();
            return $user->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật người dùng ID {$id}: " . $e->getMessage());
            throw new ApiException("Đã có lỗi xảy ra trong quá trình cập nhật.");
        }
    }


    public function deleteUser(int $id): bool
    {
        // SỬA LỖI: Gán kết quả trả về vào biến $user
        $user = $this->getUserById($id);


        if (
            ($user->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() === 1) ||
            ($user->user_id === auth()->id())
        ) {
            throw new BusinessException("Không thể xóa quản trị viên cuối cùng hoặc chính bản thân bạn.");
        }

        try {
            return $this->userRepository->delete($id);
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa người dùng ID {$id}: " . $e->getMessage());
            throw new ApiException("Đã có lỗi xảy ra trong quá trình xóa người dùng.");
        }
    }

    /**
     * Vô hiệu hóa một người dùng.
     * @param int $id
     * @return User
     * @throws BusinessException|ApiException
     */
    public function disableUser(int $id): User
    {
        $user = $this->getUserById($id);

        // Logic nghiệp vụ: không cho vô hiệu hóa admin cuối cùng hoặc chính mình
        if (
            ($user->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() === 1) ||
            ($user->user_id === auth()->id())
        ) {
            throw new BusinessException("Không thể vô hiệu hóa quản trị viên cuối cùng hoặc chính bản thân bạn.");
        }

        $this->userRepository->update($id, ['is_active' => false]);
        return $user->fresh();
    }

    /**
     * Kích hoạt lại một người dùng.
     * @param int $id
     * @return User
     * @throws BusinessException|ApiException
     */
    public function enableUser(int $id): User
    {
        $user = $this->getUserById($id);
        $this->userRepository->update($id, ['is_active' => true]);
        return $user->fresh();
    }
}
