<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{
    /**
     * UserRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**

     *
     * @param int $perPage Số lượng item mỗi trang.
     * @param string|null $search Từ khóa tìm kiếm.
     * @param array $columns Các cột cần lấy.
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, ?string $search = null, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->model->query();


        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest('user_id')->paginate($perPage, $columns);
    }

    /**
     * Tạo một người dùng mới.
     * Mật khẩu sẽ được tự động hash.
     *
     * @param array $data Dữ liệu của người dùng mới.
     * @return Model
     */
    public function create(array $data): Model
    {
      

        return parent::create($data);
    }

    /**
     * Cập nhật thông tin người dùng.
     * Mật khẩu sẽ được tự động hash nếu có sự thay đổi.
     *
     * @param int $id ID của người dùng.
     * @param array $data Dữ liệu cần cập nhật.
     * @return bool
     */
    public function update(int $id, array $data): ?Model
    {

        if (!empty($data['password_hash'])) {
            $data['password_hash'] = Hash::make($data['password_hash']);
        } else {

            unset($data['password_hash']);
        }

        return parent::update($id, $data);
    }

    /**
     * Xóa một người dùng.
     *
     * @param int $id ID của người dùng.
     * @return bool
     */
    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    /**
     * Tìm người dùng bằng email.
     *
     * @param string $email
     * @return Model|null
     */
    public function findByEmail(string $email): ?Model
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Tìm người dùng bằng ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function findById(int $id): ?Model
    {
        return parent::find($id);
    }
}
