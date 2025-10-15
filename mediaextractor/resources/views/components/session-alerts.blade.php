@if (session('success'))
    <x-alert type="success" title="Thành công!">
        {{ session('success') }}
    </x-alert>
@endif

@if (session('error'))
    <x-alert type="danger" title="Lỗi!">
        {{ session('error') }}
    </x-alert>
@endif

@if (session('warning'))
    <x-alert type="warning" title="Cảnh báo!">
        {{ session('warning') }}
    </x-alert>
@endif

@if (session('info'))
    <x-alert type="info" title="Thông báo!">
        {{ session('info') }}
    </x-alert>
@endif
