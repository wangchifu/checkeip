@extends('layouts.master')

@section('content')
<h1>GSuite 帳號登入</h1>
<div class="container">
    <div class="row no-gutters-lg">
        <div class="col-lg-12 mb-5 mb-lg-0">
            <div class="row">                
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6 mx-auto">                                                                                      
                            <form method="POST" action="{{ route('gauth') }}" class="row">
                                @csrf
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control mb-4" placeholder="Gsuite 帳號" name="username" aria-label="Recipient's username" aria-describedby="basic-addon2" autofocus tabindex="1">
                                        <span class="input-group-text mb-4" id="basic-addon2">@chc.edu.tw</span>
                                    </div>                                        
                                </div>
                                <div class="col-md-6">
                                    <input type="password" class="form-control mb-4" placeholder="密碼" name="password" id="password" tabindex="2">
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('glogin') }}"><img src="{{ route('pic') }}" class="img-fluid"></a><small class="text-secondary"> (按一下更換)</small>
                                    <input type="text" class="form-control mb-4" placeholder="上圖數字" name="chaptcha" id="chaptcha" maxlength="5" tabindex="3">
                                </div>                                    
                                <div class="col-12">
                                    <button class="btn btn-outline-primary" type="submit" tabindex="4">送出</button>
                                </div>
                            </form>
                            @include('layouts.errors')        
                        </div>
                    </div>
                </div>
            </div>
        </div>            
    </div>
</div>
@endsection