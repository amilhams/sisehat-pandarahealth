@extends('layouts.clean')

@section('title', 'Tautan Tidak Valid')

@section('styles')
<style>
    body { background-color: #0a0a0a; color: #fff; font-family: 'Inter', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; }
    .error-container { max-width: 400px; padding: 40px; background: #151515; border: 1px solid #222; border-radius: 16px; }
    .error-icon { font-size: 48px; color: #f87171; margin-bottom: 24px; }
    .error-title { font-size: 20px; font-weight: 700; margin-bottom: 12px; }
    .error-text { color: #888; font-size: 14px; line-height: 1.6; }
</style>
@endsection

@section('content')
<div class="error-container">
    <i class="fa-solid fa-circle-exclamation error-icon"></i>
    <div class="error-title">Tautan Tidak Dapat Diakses</div>
    <div class="error-text">{{ $message ?? 'Tautan ini mungkin sudah kadaluarsa atau tidak valid.' }}</div>
</div>
@endsection
