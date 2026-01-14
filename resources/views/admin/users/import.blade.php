@extends('layouts.app')

@section('title', 'Import người dùng từ Excel')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5>Import người dùng từ Excel</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf

                        <div class="mb-3">
                            <label for="file" class="form-label">Chọn file Excel</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <small class="form-text text-muted">Định dạng hỗ trợ: Excel (.xlsx, .xls) hoặc CSV</small>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sheet_index" class="form-label">Chọn Sheet (trang)</label>
                            <select class="form-control" id="sheet_index" name="sheet_index">
                                <option value="">-- Sheet hiện tại (mặc định) --</option>
                                <option value="0">Sheet 1</option>
                                <option value="1">Sheet 2</option>
                                <option value="2">Sheet 3</option>
                                <option value="3">Sheet 4</option>
                                <option value="4">Sheet 5</option>
                            </select>
                            <small class="form-text text-muted">Để trống sẽ import sheet hiện tại, hoặc chọn sheet cụ thể</small>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <h6>Cấu trúc file Excel:</h6>
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Cột Excel</th>
                                        <th>Tên cột</th>
                                        <th>Bắt buộc</th>
                                        <th>Ví dụ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>A</td>
                                        <td>Trống</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>
                                    <tr>
                                        <td>B</td>
                                        <td>STT</td>
                                        <td>-</td>
                                        <td>1, 2, 3...</td>
                                    </tr>
                                    <tr>
                                        <td>C</td>
                                        <td>Tên VĐV</td>
                                        <td>✓ Bắt buộc</td>
                                        <td>Anna Leigh Waters</td>
                                    </tr>
                                    <tr>
                                        <td>D</td>
                                        <td>Năm sinh</td>
                                        <td>Tùy chọn</td>
                                        <td>2008</td>
                                    </tr>
                                    <tr>
                                        <td>E</td>
                                        <td>Mail</td>
                                        <td>✓ Bắt buộc</td>
                                        <td>annaleighwaters@gmail.com</td>
                                    </tr>
                                    <tr>
                                        <td>F</td>
                                        <td>Quốc gia (VN)</td>
                                        <td>Tùy chọn</td>
                                        <td>Mỹ</td>
                                    </tr>
                                    <tr>
                                        <td>G</td>
                                        <td>Nơi dùng thị đấu</td>
                                        <td>-</td>
                                        <td>Đơn nữ</td>
                                    </tr>
                                    <tr>
                                        <td>H</td>
                                        <td>Hạng</td>
                                        <td>-</td>
                                        <td>1, 2, 3...</td>
                                    </tr>
                                    <tr>
                                        <td>I</td>
                                        <td>Loại (athlete_types)</td>
                                        <td>✓ Import</td>
                                        <td>athlete_international</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Import
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h6>Lưu ý khi import:</h6>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>Mail phải hợp lệ</strong> (ví dụ: user@example.com, phải chứa ký tự @)</li>
                        <li>Mail phải là duy nhất, không được trùng với email hiện có trong hệ thống</li>
                        <li>Tên VĐV (C) và Mail (E) là bắt buộc, các trường khác là tùy chọn</li>
                        <li>Mật khẩu mặc định sẽ được đặt là <code>password123</code></li>
                        <li>Người dùng sẽ được tự động phê duyệt và gán role "user"</li>
                        <li><strong>Cột I (Loại/athlete_types)</strong> sẽ được lưu vào database</li>
                        <li>Các cột khác (A, B, G, H) sẽ không được import</li>
                        <li>Chỉ bỏ qua các dòng hoàn toàn trống</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
