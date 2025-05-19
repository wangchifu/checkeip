@extends('layouts.master')

@section('content')
<?php $att = session('gsuite_login'); ?>
@if(!empty($att))
    @if(($att['name']=="王麒富" and $att['school_code']=="074628") or 
        ($att['name']=="林哲民" and $att['school_code']=="079998") or
        ($att['name']=="林金玉" and $att['school_code']=="079998") or
        ($att['name']=="林政言" and $att['school_code']=="079998"))
        <div class="container mt-5">            
            <link href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.2/chosen.min.css" rel="stylesheet" />
            <link href="{{ asset('css/component-chosen.min.css') }}" rel="stylesheet" />
            <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.2/chosen.jquery.min.js"></script>
            <form action="{{ route('upload_csv') }}" method="get" id="schoolForm">
                @csrf
                <div class="mb-3">                    
                    <select class="form-select search_selet" id="school" name="school" required>
                        <option value="" disabled selected>1.請選擇學校</option>
                        @foreach($schools_id as $key => $school)
                            <option value="{{ $key }}">({{ $key }}) {{ $school }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">2.按查詢</button>
            </form>            
            <script>
                $( ".search_selet" ).chosen({
                                    search_contains: true,
                                });
                document.getElementById('schoolForm').addEventListener('submit', function(event) {
                    event.preventDefault(); // 防止預設提交
                    const selectedValue = document.getElementById('school').value;
                    if (selectedValue) {
                        // 用 Laravel 的路由前綴手動組裝 URL
                        const url = `{{ url('schools') }}/${selectedValue}`;
                        this.action = url;
                        this.submit(); // 提交表單
                    }
                });
            </script>
        </div>   
        <hr>
        <h2 class="mb-4">{{ $school_name }}已登記列表</h2>
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
@endif

@endsection