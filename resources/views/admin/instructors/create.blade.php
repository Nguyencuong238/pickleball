@extends('layouts.app')
@section('title', 'Thêm giảng viên')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Thêm giảng viên</h2>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.instructors.store') }}" method="POST" enctype="multipart/form-data" id="instructorForm">
                    @csrf

                    <!-- THÔNG TIN CƠ BẢN -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên giảng viên <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Nhập tên giảng viên" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Image -->
                            <div class="mb-3">
                                <label for="image" class="form-label">Ảnh</label>
                                <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror"
                                    accept="image/*">
                                <div class="mt-2">
                                    <img id="preview-image" src="#" alt="Preview Image"
                                        style="display:none; max-width: 200px;">
                                </div>
                                @error('image')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Bio -->
                            <div class="mb-3">
                                <label for="bio" class="form-label">Tiêu đề (Bio)</label>
                                <input type="text" name="bio" id="bio" class="form-control @error('bio') is-invalid @enderror"
                                    value="{{ old('bio') }}" placeholder="Ví dụ: Huấn luyện viên Pickleball chuyên nghiệp">
                                @error('bio')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả chi tiết</label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                    style="min-height:150px;" placeholder="Mô tả chi tiết về giảng viên">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Experience Years -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="experience_years" class="form-label">Năm kinh nghiệm</label>
                                        <input type="number" name="experience_years" id="experience_years" class="form-control @error('experience_years') is-invalid @enderror"
                                            value="{{ old('experience_years', 0) }}" min="0">
                                        @error('experience_years')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Student Count -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="student_count" class="form-label">Số học viên</label>
                                        <input type="number" name="student_count" id="student_count" class="form-control @error('student_count') is-invalid @enderror"
                                            value="{{ old('student_count', 0) }}" min="0">
                                        @error('student_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Total Hours -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="total_hours" class="form-label">Tổng giờ dạy</label>
                                        <input type="number" name="total_hours" id="total_hours" class="form-control @error('total_hours') is-invalid @enderror"
                                            value="{{ old('total_hours', 0) }}" min="0">
                                        @error('total_hours')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Price per Session -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price_per_session" class="form-label">Giá per buổi (đ)</label>
                                        <input type="number" name="price_per_session" id="price_per_session" class="form-control @error('price_per_session') is-invalid @enderror"
                                            value="{{ old('price_per_session', 0) }}" min="0">
                                        @error('price_per_session')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Ward -->
                            <div class="mb-3">
                                <label for="ward" class="form-label">Phường</label>
                                <input type="text" name="ward" id="ward" class="form-control @error('ward') is-invalid @enderror"
                                    value="{{ old('ward') }}" placeholder="Nhập tên phường">
                                @error('ward')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Province -->
                            <div class="mb-3">
                                <label for="province_id" class="form-label">Tỉnh, TP</label>
                                <select name="province_id" id="province_id" class="form-control @error('province_id') is-invalid @enderror">
                                    <option value="">-- Chọn tỉnh, TP --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('province_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Contact Info -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Điện thoại</label>
                                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                            value="{{ old('phone') }}" placeholder="0123456789">
                                        @error('phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email') }}">
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Zalo -->
                            <div class="mb-3">
                                <label for="zalo" class="form-label">Zalo</label>
                                <input type="text" name="zalo" id="zalo" class="form-control @error('zalo') is-invalid @enderror"
                                    value="{{ old('zalo') }}" placeholder="Zalo ID hoặc số điện thoại">
                                @error('zalo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- KINH NGHIỆM GIẢNG DẠY -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Kinh nghiệm giảng dạy</h5>
                        </div>
                        <div class="card-body">
                            <div id="experiences-container">
                                <div class="experience-item mb-3 p-3 border rounded" data-index="0">
                                    <div class="mb-2">
                                        <label class="form-label">Chức vụ / Tiêu đề</label>
                                        <input type="text" name="experiences[0][title]" class="form-control" placeholder="Ví dụ: Huấn luyện viên chính">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Tổ chức</label>
                                        <input type="text" name="experiences[0][organization]" class="form-control" placeholder="Ví dụ: CLB Pickleball Saigon Elite">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Năm bắt đầu</label>
                                                <input type="number" name="experiences[0][start_year]" class="form-control" placeholder="2020" min="1900">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Năm kết thúc</label>
                                                <input type="number" name="experiences[0][end_year]" class="form-control" placeholder="2025 (nếu hiện tại để trống)">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Mô tả</label>
                                        <textarea name="experiences[0][description]" class="form-control" style="min-height: 100px;" placeholder="Mô tả công việc"></textarea>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-experience">Xóa</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addExperience()">+ Thêm kinh nghiệm</button>
                        </div>
                    </div>

                    <!-- CHỨNG CHỈ & THÀNH TÍCH -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Chứng chỉ & Thành tích</h5>
                        </div>
                        <div class="card-body">
                            <div id="certifications-container">
                                <div class="certification-item mb-3 p-3 border rounded" data-index="0">
                                    <div class="mb-2">
                                        <label class="form-label">Tên chứng chỉ</label>
                                        <input type="text" name="certifications[0][title]" class="form-control" placeholder="Ví dụ: IPTPA Certified Coach">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Tổ chức cấp</label>
                                                <input type="text" name="certifications[0][issuer]" class="form-control" placeholder="Ví dụ: International Pickleball Association">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Năm cấp</label>
                                                <input type="number" name="certifications[0][year]" class="form-control" placeholder="2020" min="1900">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-check-label">
                                            <input type="checkbox" name="certifications[0][is_award]" class="form-check-input" value="1">
                                            Đây là một giải thưởng?
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-certification">Xóa</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addCertification()">+ Thêm chứng chỉ</button>
                        </div>
                    </div>

                    <!-- PHƯƠNG PHÁP GIẢNG DẠY -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Phương pháp giảng dạy</h5>
                        </div>
                        <div class="card-body">
                            <div id="methods-container">
                                <div class="method-item mb-3 p-3 border rounded" data-index="0">
                                    <div class="mb-2">
                                        <label class="form-label">Tên phương pháp</label>
                                        <input type="text" name="teaching_methods[0][title]" class="form-control" placeholder="Ví dụ: Cá nhân hóa">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Mô tả</label>
                                        <textarea name="teaching_methods[0][description]" class="form-control" style="min-height: 80px;" placeholder="Mô tả phương pháp"></textarea>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-method">Xóa</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addMethod()">+ Thêm phương pháp</button>
                        </div>
                    </div>

                    <!-- GÓI HỌC -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Gói học & Giá</h5>
                        </div>
                        <div class="card-body">
                            <div id="packages-container">
                                <div class="package-item mb-3 p-3 border rounded" data-index="0">
                                    <div class="mb-2">
                                        <label class="form-label">Tên gói</label>
                                        <input type="text" name="packages[0][name]" class="form-control" placeholder="Ví dụ: Buổi lẻ, Gói 4 buổi">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Mô tả</label>
                                        <input type="text" name="packages[0][description]" class="form-control" placeholder="Mô tả gói">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Giá (đ)</label>
                                                <input type="number" name="packages[0][price]" class="form-control" placeholder="500000" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Giảm giá (%)</label>
                                                <input type="number" name="packages[0][discount_percent]" class="form-control" placeholder="0" min="0" max="100">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Số buổi</label>
                                                <input type="number" name="packages[0][sessions_count]" class="form-control" placeholder="1" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-check-label">
                                            <input type="checkbox" name="packages[0][is_active]" class="form-check-input" value="1" checked>
                                            Kích hoạt
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-package">Xóa</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addPackage()">+ Thêm gói</button>
                        </div>
                    </div>

                    <!-- KHU VỰC DẠY -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Khu vực dạy</h5>
                        </div>
                        <div class="card-body">
                            <div id="locations-container">
                                <div class="location-item mb-3 p-3 border rounded" data-index="0">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Quận/Huyện</label>
                                                <input type="text" name="locations[0][district]" class="form-control" placeholder="Ví dụ: Quận 2">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Thành phố</label>
                                                <input type="text" name="locations[0][city]" class="form-control" placeholder="Ví dụ: TP. HCM">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Tên sân</label>
                                        <textarea name="locations[0][venues]" class="form-control" style="min-height: 80px;" placeholder="Ví dụ: Sân Rạch Chiếc, Sân An Phú"></textarea>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-location">Xóa</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addLocation()">+ Thêm khu vực</button>
                        </div>
                    </div>

                    <!-- LỊCH DẠY -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Lịch dạy</h5>
                        </div>
                        <div class="card-body">
                            <div id="schedules-container">
                                <div class="schedule-item mb-3 p-3 border rounded" data-index="0">
                                    <div class="mb-2">
                                        <label class="form-label">Các ngày</label>
                                        <input type="text" name="schedules[0][days]" class="form-control" placeholder="Ví dụ: Thứ 2 - Thứ 6">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Giờ học</label>
                                        <input type="text" name="schedules[0][time_slots]" class="form-control" placeholder="Ví dụ: 06:00 - 08:00, 17:00 - 21:00">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-schedule">Xóa</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addSchedule()">+ Thêm lịch</button>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Tạo</button>
                        <a href="{{ route('admin.instructors.index') }}" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let experienceCount = 1;
        let certificationCount = 1;
        let methodCount = 1;
        let packageCount = 1;
        let locationCount = 1;
        let scheduleCount = 1;

        // Experience
        function addExperience() {
            const container = document.getElementById('experiences-container');
            const html = `
                <div class="experience-item mb-3 p-3 border rounded" data-index="${experienceCount}">
                    <div class="mb-2">
                        <label class="form-label">Chức vụ / Tiêu đề</label>
                        <input type="text" name="experiences[${experienceCount}][title]" class="form-control" placeholder="Ví dụ: Huấn luyện viên chính">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tổ chức</label>
                        <input type="text" name="experiences[${experienceCount}][organization]" class="form-control" placeholder="Ví dụ: CLB Pickleball Saigon Elite">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Năm bắt đầu</label>
                                <input type="number" name="experiences[${experienceCount}][start_year]" class="form-control" placeholder="2020" min="1900">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Năm kết thúc</label>
                                <input type="number" name="experiences[${experienceCount}][end_year]" class="form-control" placeholder="2025">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mô tả</label>
                        <textarea name="experiences[${experienceCount}][description]" class="form-control" style="min-height: 100px;" placeholder="Mô tả công việc"></textarea>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-experience">Xóa</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            experienceCount++;
            attachRemoveHandlers();
        }

        // Certification
        function addCertification() {
            const container = document.getElementById('certifications-container');
            const html = `
                <div class="certification-item mb-3 p-3 border rounded" data-index="${certificationCount}">
                    <div class="mb-2">
                        <label class="form-label">Tên chứng chỉ</label>
                        <input type="text" name="certifications[${certificationCount}][title]" class="form-control" placeholder="Ví dụ: IPTPA Certified Coach">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Tổ chức cấp</label>
                                <input type="text" name="certifications[${certificationCount}][issuer]" class="form-control" placeholder="Ví dụ: International Pickleball Association">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Năm cấp</label>
                                <input type="number" name="certifications[${certificationCount}][year]" class="form-control" placeholder="2020" min="1900">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-check-label">
                            <input type="checkbox" name="certifications[${certificationCount}][is_award]" class="form-check-input" value="1">
                            Đây là một giải thưởng?
                        </label>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-certification">Xóa</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            certificationCount++;
            attachRemoveHandlers();
        }

        // Method
        function addMethod() {
            const container = document.getElementById('methods-container');
            const html = `
                <div class="method-item mb-3 p-3 border rounded" data-index="${methodCount}">
                    <div class="mb-2">
                        <label class="form-label">Tên phương pháp</label>
                        <input type="text" name="teaching_methods[${methodCount}][title]" class="form-control" placeholder="Ví dụ: Cá nhân hóa">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mô tả</label>
                        <textarea name="teaching_methods[${methodCount}][description]" class="form-control" style="min-height: 80px;" placeholder="Mô tả phương pháp"></textarea>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-method">Xóa</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            methodCount++;
            attachRemoveHandlers();
        }

        // Package
        function addPackage() {
            const container = document.getElementById('packages-container');
            const html = `
                <div class="package-item mb-3 p-3 border rounded" data-index="${packageCount}">
                    <div class="mb-2">
                        <label class="form-label">Tên gói</label>
                        <input type="text" name="packages[${packageCount}][name]" class="form-control" placeholder="Ví dụ: Buổi lẻ, Gói 4 buổi">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mô tả</label>
                        <input type="text" name="packages[${packageCount}][description]" class="form-control" placeholder="Mô tả gói">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Giá (đ)</label>
                                <input type="number" name="packages[${packageCount}][price]" class="form-control" placeholder="500000" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Giảm giá (%)</label>
                                <input type="number" name="packages[${packageCount}][discount_percent]" class="form-control" placeholder="0" min="0" max="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Số buổi</label>
                                <input type="number" name="packages[${packageCount}][sessions_count]" class="form-control" placeholder="1" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-check-label">
                            <input type="checkbox" name="packages[${packageCount}][is_active]" class="form-check-input" value="1" checked>
                            Kích hoạt
                        </label>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-package">Xóa</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            packageCount++;
            attachRemoveHandlers();
        }

        // Location
        function addLocation() {
            const container = document.getElementById('locations-container');
            const html = `
                <div class="location-item mb-3 p-3 border rounded" data-index="${locationCount}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Quận/Huyện</label>
                                <input type="text" name="locations[${locationCount}][district]" class="form-control" placeholder="Ví dụ: Quận 2">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Thành phố</label>
                                <input type="text" name="locations[${locationCount}][city]" class="form-control" placeholder="Ví dụ: TP. HCM">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tên sân</label>
                        <textarea name="locations[${locationCount}][venues]" class="form-control" style="min-height: 80px;" placeholder="Ví dụ: Sân Rạch Chiếc, Sân An Phú"></textarea>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-location">Xóa</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            locationCount++;
            attachRemoveHandlers();
        }

        // Schedule
        function addSchedule() {
            const container = document.getElementById('schedules-container');
            const html = `
                <div class="schedule-item mb-3 p-3 border rounded" data-index="${scheduleCount}">
                    <div class="mb-2">
                        <label class="form-label">Các ngày</label>
                        <input type="text" name="schedules[${scheduleCount}][days]" class="form-control" placeholder="Ví dụ: Thứ 2 - Thứ 6">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Giờ học</label>
                        <input type="text" name="schedules[${scheduleCount}][time_slots]" class="form-control" placeholder="Ví dụ: 06:00 - 08:00, 17:00 - 21:00">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-schedule">Xóa</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            scheduleCount++;
            attachRemoveHandlers();
        }

        // Attach remove handlers
        function attachRemoveHandlers() {
            document.querySelectorAll('.remove-experience').forEach(btn => {
                btn.onclick = function() { this.closest('.experience-item').remove(); };
            });
            document.querySelectorAll('.remove-certification').forEach(btn => {
                btn.onclick = function() { this.closest('.certification-item').remove(); };
            });
            document.querySelectorAll('.remove-method').forEach(btn => {
                btn.onclick = function() { this.closest('.method-item').remove(); };
            });
            document.querySelectorAll('.remove-package').forEach(btn => {
                btn.onclick = function() { this.closest('.package-item').remove(); };
            });
            document.querySelectorAll('.remove-location').forEach(btn => {
                btn.onclick = function() { this.closest('.location-item').remove(); };
            });
            document.querySelectorAll('.remove-schedule').forEach(btn => {
                btn.onclick = function() { this.closest('.schedule-item').remove(); };
            });
        }

        // Preview image
        document.getElementById('image')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('preview-image').src = event.target.result;
                    document.getElementById('preview-image').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Init
        attachRemoveHandlers();
    </script>
@endsection
