@extends('layouts.master')

@section('content')
<?php $att = session('gsuite_login'); ?>
@if(!empty($att))
    @if(($att['name']=="王麒富" and $att['school_code']=="074628") or ($att['name']=="林哲民" and $att['school_code']=="079998"))
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
            <p>以下為身分證格式有問題者</p>
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>序號</th>
                        <th>身分證</th>
                        <th>gsuite帳號</th>
                        <th>日期</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($error_users as $user)
                    <tr style="word-break: break-word;overflow-wrap: break-word;">            
                        <td>{{ $loop->iteration }}</td>            
                        <td>{{ $user['pid'] }}</td>
                        <?php $gsuite = str_replace("@chc.edu.tw","",$user['gsuite']); ?>
                        <?php
                            $maybe_user = \App\Models\StaffView::where('gsuite_account', $gsuite)->first(); 
                            $schools_id = config('ge.schools_id');                           
                        ?>
                        <td>{{ $gsuite }}
                            @if(!empty($maybe_user))       
                                <br><span class="text-secondary small">(可能是：<br>
                                @if(isset($schools_id[$maybe_user->staff_sid]))
                                    {{ $schools_id[$maybe_user->staff_sid]}}<br>                                    
                                @else
                                    {{ $maybe_user->staff_sid }}
                                @endif                                
                                {{ $maybe_user->staff_title }}<br>                                                     
                                {{ $maybe_user->staff_name }})</span>
                            @endif                            
                        </td>
                        <td>{{ $user['date'] }}</td>
                    </tr>                    
                @endforeach
                </tbody>
            </table>
            <hr>
        @endif
    </div>        
    @endif
    <h2 class="mb-4">{{ $att['school_name'] }}已登記列表</h2>
    <p class="text-danger">*若確定有填寫表單，卻沒有在下列，很可能是因為身分證填錯！</p>
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
            <?php   
                    if($user['agree']=="不同意"){
                        $css = "  font-weight: bold;color: red;";
                        $img = "<img src='".asset('images/no.png')."' width='15'>";
                    }elseif($user['agree']=="同意"){
                        $css = "";
                        $img = "";
                    }
                ?>
            <tr style="<?= $css ?> word-break: break-word;overflow-wrap: break-word;">
                <td>{!! $img !!}{{ $n }}</td>
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