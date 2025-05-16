@extends('layouts.master')

@section('content')
<?php $att = session('gsuite_login'); ?>
@if(!empty($att))
    @if($att['name']=="王麒富" and $att['school_code']=="074628")
    <div class="container mt-5">
        <h2 class="mb-4">上傳 CSV 檔案</h2>
        <form action="{{ route('upload_csv') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="csvFile" class="form-label">選擇 CSV 檔案：</label>
                <input class="form-control" type="file" id="csvFile" name="csvFile" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary">上傳</button>
        </form>
        @if(is_file(storage_path('app/privacy/all.csv')))
            <p>已有檔案上傳</p>
        @endif
    </div>        
    @endif
    <h2 class="mb-4">已登記列表</h2>
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>序號</th>
                <th>身分證</th>
                <th>同意</th>
                <th>職稱</th>
                <th>姓名</th>
                <th>Gsuite 帳號</th>
                <th>填寫日期</th>
            </tr>
        </thead>
        <tbody>
            <?php $n = 1; ?>
            <?php foreach ($check_users as $user): ?>
            <tr>
                <td>{{ $n }}</td>
                <td><?= htmlspecialchars($user['pid']) ?></td>
                <td><?= htmlspecialchars($user['agree']) ?></td>
                <td><?= htmlspecialchars($user['title']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['gsuite_account']) ?></td>
                <td><?= htmlspecialchars($user['date']) ?></td>
            </tr>
            <?php $n++; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
@endif

@endsection