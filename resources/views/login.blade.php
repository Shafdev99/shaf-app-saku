@extends('layout.auth')
@section('title', 'Login')
@section('content')
    <div class="error mx-auto mt-2 card card-login">
        <h1 class="login-title text-center mb-4">Login.</h1>
        <form action="{{ url('/auth') }}" method="POST" class="form-login d-flex flex-column">
            @csrf
            @if (session('gagal'))
                <div class="alert alert-danger">{{ session('gagal') }}</div>
            @endif
            @if (session('info'))
                <div class="alert alert-success">{!! session('info') !!}</div>
            @endif
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control form-borderless" placeholder="Masukan username!" id="username"
                    name="username" required>

                <label for="password" class="form-label mt-4">Password</label>
                <div class="d-flex position-relative group-show-password">
                    <input type="password" id="password" class="form-control form-borderless input-show-password"
                        placeholder="Masukan password!" name="password" required>
                    <button class="btn btn-show-password" type="button" onclick="showPassword(this)"
                        aria-label="Tampilkan password">
                        <i class="bi bi-eye-fill"></i>
                    </button>
                </div>

                <label for="tahun_ajar_id" class="form-label mt-4">Semester</label>
                <select name="tahun_ajar_id" id="tahun_ajar_id" class="form-select form-borderless">
                    @foreach ($tahunAjar as $item)
                        <option value="{{ $item->id }}">{{ $item->tahun_ajar . ' ' . Str::ucfirst($item->semester) }}
                        </option>
                    @endforeach
                </select>

                <div class="mt-4 form-check remember-btn d-flex justify-content-between">
                    <input type="checkbox" class="form-check-input" name="remember" value="true" id="remember">
                    <label class="form-check-label me-auto ms-2" for="remember">Ingat saya!</label>
                    <a href="{{ route('resetPassword') }}">Lupa kata sandi?</a>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mb-3 login-btn">Login</button>
        </form>
    </div>

@endsection
